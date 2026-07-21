import os

workspace = r"c:\Users\HP\Documents\Jews-world Backend\picme225.com_backend"
print("Searching for play store links...")
for root, dirs, files in os.walk(workspace):
    for f in files:
        if f.endswith('.php') or f.endswith('.json'):
            path = os.path.join(root, f)
            try:
                with open(path, 'r', encoding='utf-8', errors='ignore') as file:
                    content = file.read()
                    if 'play.google.com' in content or 'play_store' in content:
                        print(f"Found in: {path}")
            except:
                pass
