"""
fix_seeders_final.py  (v2 - plain string replacement, no regex)
Replaces the broken double-patched FK check lines with clean versions.
"""
import os

SEEDER_DIR = "database/seeders"

# Exact broken strings to find (copy-pasted from actual file content)
BROKEN_DISABLE = (
    "if (\\DB::getDriverName() === 'mysql') { \\if (\\DB::getDriverName() === 'mysql') { "
    "\\DB::statement('SET FOREIGN_KEY_CHECKS=0;'); } elseif (\\DB::getDriverName() === 'pgsql') { "
    "\\DB::statement(\"SET session_replication_role = 'replica';\"); } } elseif "
    "(\\DB::getDriverName() === 'pgsql') { \\DB::statement(\"SET session_replication_role = 'replica';\"); }"
)

BROKEN_ENABLE = (
    "if (\\DB::getDriverName() === 'mysql') { \\if (\\DB::getDriverName() === 'mysql') { "
    "\\DB::statement('SET FOREIGN_KEY_CHECKS=1;'); } elseif (\\DB::getDriverName() === 'pgsql') { "
    "\\DB::statement(\"SET session_replication_role = 'origin';\"); } } elseif "
    "(\\DB::getDriverName() === 'pgsql') { \\DB::statement(\"SET session_replication_role = 'origin';\"); }"
)

CLEAN_DISABLE = (
    "if (\\DB::getDriverName() === 'pgsql') { \\DB::statement(\"SET session_replication_role = 'replica';\"); } "
    "else { \\DB::statement('SET FOREIGN_KEY_CHECKS=0;'); }"
)

CLEAN_ENABLE = (
    "if (\\DB::getDriverName() === 'pgsql') { \\DB::statement(\"SET session_replication_role = 'origin';\"); } "
    "else { \\DB::statement('SET FOREIGN_KEY_CHECKS=1;'); }"
)

total_fixed = 0

for fname in sorted(os.listdir(SEEDER_DIR)):
    if not fname.endswith(".php"):
        continue
    path = os.path.join(SEEDER_DIR, fname)
    
    try:
        with open(path, "r", encoding="utf-8", errors="replace") as f:
            content = f.read()
    except Exception as e:
        print(f"ERROR reading {fname}: {e}")
        continue

    original = content
    
    # Replace broken patterns with clean ones
    content = content.replace(BROKEN_DISABLE, CLEAN_DISABLE)
    content = content.replace(BROKEN_ENABLE,  CLEAN_ENABLE)

    if content != original:
        with open(path, "w", encoding="utf-8") as f:
            f.write(content)
        total_fixed += 1
        print(f"  Fixed: {fname}")
    else:
        # Check if file still has a problem
        if "\\if" in content:
            print(f"  WARNING still broken: {fname}")
        elif "FOREIGN_KEY_CHECKS" in content or "session_replication" in content:
            print(f"  Has FK handling (OK): {fname}")
        else:
            print(f"  No FK handling (OK): {fname}")

print(f"\n=== Total fixed: {total_fixed} files ===")
