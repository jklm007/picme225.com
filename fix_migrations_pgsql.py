#!/usr/bin/env python3
"""
Script de correction en masse des migrations MySQL vers PostgreSQL-compatibles.
Wrap les instructions MySQL-spécifiques dans des blocs conditionnels selon le driver.
"""

import os
import re
import glob

MIGRATIONS_DIR = os.path.join(os.path.dirname(__file__), 'database', 'migrations')

# Patterns MySQL-spécifiques à wrapper
MYSQL_PATTERNS = [
    'MODIFY ',
    'MODIFY\n',
    'SHOW INDEX FROM',
    'SHOW TABLES',
    'SHOW COLUMNS',
]

def needs_fixing(content):
    for pattern in MYSQL_PATTERNS:
        if pattern in content:
            return True
    return False

def fix_show_index(content, filename):
    """
    Replace SHOW INDEX FROM table WHERE Key_name = 'X'
    with pg_indexes equivalent
    """
    # Pattern: DB::select("SHOW INDEX FROM table WHERE Key_name = 'index_name'")
    def replace_show_index(m):
        table = m.group(1)
        index_name = m.group(2)
        return (
            f'DB::select("SELECT indexname FROM pg_indexes WHERE tablename = \'{table}\' AND indexname = \'{index_name}\'")'
            if 'pgsql' in content or True  # always replace
            else m.group(0)
        )
    
    # Match: DB::select("SHOW INDEX FROM tablename WHERE Key_name = 'indexname'")
    pattern = r'DB::select\s*\(\s*"SHOW INDEX FROM\s+(\w+)\s+WHERE\s+Key_name\s*=\s*\'([^\']+)\'"\s*\)'
    new_content = re.sub(pattern, replace_show_index, content)
    if new_content != content:
        print(f"  [SHOW INDEX] Fixed in {filename}")
    return new_content

def fix_modify_statements(content, filename):
    """
    Wrap DB::statement("ALTER TABLE ... MODIFY ...") to be a no-op for PostgreSQL.
    PostgreSQL uses TEXT/VARCHAR columns that don't need ENUM modification.
    """
    # Find all DB::statement blocks that contain MODIFY
    # Pattern: DB::statement("ALTER TABLE ... MODIFY ...")
    # We wrap the entire statement in a mysql-only check
    
    modified = False
    
    # Pattern for: DB::statement("...MODIFY...");
    # Including multi-line strings
    pattern = r'([ \t]*)((?:\\Illuminate\\Support\\Facades\\)?DB::statement\s*\(\s*"[^"]*?MODIFY[^"]*?"\s*\)\s*;)'
    
    def wrap_statement(m):
        nonlocal modified
        indent = m.group(1)
        stmt = m.group(2)
        # Check if already wrapped
        if 'getDriverName' in content[max(0, m.start()-200):m.start()]:
            return m.group(0)
        modified = True
        return (
            f'{indent}if (DB::connection()->getDriverName() === \'mysql\') {{\n'
            f'{indent}    {stmt.strip()}\n'
            f'{indent}}}'
        )
    
    new_content = re.sub(pattern, wrap_statement, content, flags=re.DOTALL)
    
    # Also handle MODIFY COLUMN pattern in multi-line heredoc style
    # Pattern for: DB::statement("ALTER TABLE\n ... MODIFY COLUMN ...\n...")
    pattern2 = r'([ \t]*)((?:\\Illuminate\\Support\\Facades\\)?DB::statement\s*\(\s*"[^"]*?\n[^"]*?MODIFY COLUMN[^"]*?"\s*\)\s*;)'
    new_content = re.sub(pattern2, wrap_statement, new_content, flags=re.DOTALL)
    
    if modified:
        print(f"  [MODIFY] Wrapped in {filename}")
    
    return new_content

def fix_spatial_show_index(content, filename):
    """Fix SHOW INDEX in add_spatial_index migration"""
    # This one uses DB::select("SHOW INDEX FROM providers ...")
    pattern = r'\\\$indexes\s*=\s*\\DB::select\s*\(\s*"SHOW INDEX FROM\s+(\w+)[^"]*?"\s*\)'
    
    def replace_spatial(m):
        table = m.group(1)
        return (
            f'$indexes = \\DB::select("SELECT indexname as Key_name FROM pg_indexes WHERE tablename = \'{table}\' AND indextype = \'gist\'")'
        )
    
    new_content = re.sub(pattern, replace_spatial, content, flags=re.DOTALL)
    if new_content != content:
        print(f"  [SHOW INDEX spatial] Fixed in {filename}")
    return new_content

def process_file(filepath):
    filename = os.path.basename(filepath)
    
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    if not needs_fixing(content):
        return False
    
    print(f"\nProcessing: {filename}")
    
    original = content
    content = fix_show_index(content, filename)
    content = fix_spatial_show_index(content, filename)
    content = fix_modify_statements(content, filename)
    
    if content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"  -> SAVED")
        return True
    else:
        print(f"  -> No changes made (patterns might be too complex, manual fix needed)")
        return False

def main():
    files = sorted(glob.glob(os.path.join(MIGRATIONS_DIR, '*.php')))
    print(f"Scanning {len(files)} migration files...")
    
    fixed = 0
    skipped = 0
    
    for filepath in files:
        if process_file(filepath):
            fixed += 1
        else:
            skipped += 1
    
    print(f"\n=== Summary ===")
    print(f"Fixed: {fixed}")
    print(f"Skipped/No change: {skipped}")
    print(f"Total: {len(files)}")

if __name__ == '__main__':
    main()
