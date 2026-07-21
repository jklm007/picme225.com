"""
fix_seeders_clean.py
Properly cleans and fixes all seeder files:
1. Removes all broken/duplicated FOREIGN_KEY_CHECKS patterns
2. Replaces with clean, correct PostgreSQL-compatible version
"""
import os
import re

seeder_dir = "database/seeders"
fixed_count = 0
skipped = []

# Replacement blocks
PGSQL_DISABLE = "if (\\DB::getDriverName() === 'pgsql') { \\DB::statement(\"SET session_replication_role = 'replica';\"); } elseif (\\DB::getDriverName() === 'mysql') { \\DB::statement('SET FOREIGN_KEY_CHECKS=0;'); }"
PGSQL_ENABLE  = "if (\\DB::getDriverName() === 'pgsql') { \\DB::statement(\"SET session_replication_role = 'origin';\"); } elseif (\\DB::getDriverName() === 'mysql') { \\DB::statement('SET FOREIGN_KEY_CHECKS=1;'); }"

for root, dirs, files in os.walk(seeder_dir):
    for fname in files:
        if not fname.endswith(".php"):
            continue
        path = os.path.join(root, fname)
        
        try:
            with open(path, "r", encoding="utf-8") as f:
                content = f.read()
        except UnicodeDecodeError:
            try:
                with open(path, "r", encoding="latin-1") as f:
                    content = f.read()
            except Exception as e:
                skipped.append(f"{fname}: {e}")
                continue

        original = content

        # STEP 1: Remove ALL existing FOREIGN_KEY_CHECKS / session_replication_role lines
        # These can be in many mangled forms due to multiple fix runs
        # Pattern: any full statement line(s) containing these keywords
        patterns_to_remove = [
            # Any line with FOREIGN_KEY_CHECKS
            r'[ \t]*(?:\\?\\?DB::|DB::)statement\([\'"]SET FOREIGN_KEY_CHECKS[=\w]*[;\'"]+\)[;]?\s*\n?',
            # Any line with session_replication_role  
            r'[ \t]*(?:\\?\\?DB::|DB::)statement\([\'"]SET session_replication_role[^)]*\)[;]?\s*\n?',
            # Full if-elseif blocks about FOREIGN_KEY_CHECKS (single line)
            r'[ \t]*if \(\\?DB::getDriverName\(\)[^}]+FOREIGN_KEY[^}]*\}[^\n]*\n?',
            r'[ \t]*if \(\\?DB::getDriverName\(\)[^}]+session_replication[^}]*\}[^\n]*\n?',
            # Broken \if patterns
            r'[ \t]*\\if[^\n]*\n?',
        ]
        
        for pat in patterns_to_remove:
            content = re.sub(pat, '', content, flags=re.MULTILINE)

        # STEP 2: Check if file has any DB operations that need FK protection
        # Only add FK handling if the file uses DB:: and does truncate/insert operations
        needs_fk = bool(re.search(r'DB::table|DB::insert|truncate\(\)', content))
        
        if needs_fk and 'session_replication_role' not in content and 'FOREIGN_KEY_CHECKS' not in content:
            # Insert FK disable right after the opening of run() method
            # Find the run() method opening brace
            run_match = re.search(r'(public function run\([^)]*\)[^{]*\{)\s*\n', content)
            if run_match:
                insert_pos = run_match.end()
                content = (
                    content[:insert_pos] + 
                    f"        {PGSQL_DISABLE}\n\n" +
                    content[insert_pos:]
                )
                # Find the closing brace of run() and add re-enable before it
                # Simple approach: add before the last closing } in file
                # More precise: find the last DB statement block end
                last_brace = content.rfind('\n    }')
                if last_brace != -1:
                    content = (
                        content[:last_brace] +
                        f"\n\n        {PGSQL_ENABLE}" +
                        content[last_brace:]
                    )

        if content != original:
            with open(path, "w", encoding="utf-8") as f:
                f.write(content)
            fixed_count += 1
            print(f"Fixed: {fname}")
        else:
            print(f"OK (no change): {fname}")

print(f"\n=== Done: {fixed_count} files fixed ===")
if skipped:
    print("Skipped:", skipped)
