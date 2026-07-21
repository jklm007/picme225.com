import os

workspace = r"c:\Users\HP\Documents\Jews-world Backend\picme225.com_backend"
print("Searching for get_osrm_routing...")

for root, dirs, files in os.walk(workspace):
    # prune skipped dirs
    dirs[:] = [d for d in dirs if d not in {'vendor', 'node_modules', 'storage', 'public', '.git', '.agents', '.gemini'}]
    for f in files:
        if f.endswith('.php'):
            path = os.path.join(root, f)
            try:
                with open(path, 'r', encoding='utf-8', errors='ignore') as file:
                    content = file.read()
                    if 'function get_osrm_routing' in content or 'get_osrm_routing' in content and 'function' in content:
                        print(f"Found in: {path}")
            except:
                pass
