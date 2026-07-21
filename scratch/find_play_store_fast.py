import os

workspace = r"c:\Users\HP\Documents\Jews-world Backend\picme225.com_backend"
print("Searching for play store links quickly...")
skip_dirs = {'vendor', 'node_modules', 'storage', 'public', '.git', '.agents', '.gemini'}

for root, dirs, files in os.walk(workspace):
    # prune skipped dirs
    dirs[:] = [d for d in dirs if d not in skip_dirs]
    for f in files:
        if f.endswith('.php') or f.endswith('.json'):
            path = os.path.join(root, f)
            try:
                with open(path, 'r', encoding='utf-8', errors='ignore') as file:
                    content = file.read()
                    if 'play.google.com' in content or 'store/apps' in content:
                        print(f"Found in: {path}")
            except:
                pass
print("Search done.")
