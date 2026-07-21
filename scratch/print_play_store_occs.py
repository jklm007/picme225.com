import re

files = [
    r"c:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\resources\views\user\layout\app.blade.php",
    r"c:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\resources\views\drive.blade.php"
]

for filepath in files:
    print(f"=== {filepath} ===")
    with open(filepath, 'r', encoding='utf-8') as f:
        for i, line in enumerate(f, 1):
            if 'play.google.com' in line or 'store/apps' in line:
                print(f"Line {i}: {line.strip()}")
