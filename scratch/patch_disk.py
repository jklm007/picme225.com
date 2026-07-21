import os
import re

controllers_dir = r"c:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\app\Http\Controllers"

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    original = content
    # Replace .store('path', 'public') with .store('path')
    content = re.sub(r"->store\(([^,]+),\s*'public'\)", r"->store(\1)", content)
    # Replace Storage::disk('public')-> with Storage::
    content = re.sub(r"Storage::disk\('public'\)->", r"Storage::", content)

    if content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Patched {os.path.basename(filepath)}")

for root, _, files in os.walk(controllers_dir):
    for file in files:
        if file.endswith('.php'):
            patch_file(os.path.join(root, file))

print("Patching complete.")
