import os

workspace = r"c:\Users\HP\Documents\Jews-world Backend\picme225.com_backend"
route_files = [os.path.join(workspace, 'routes', 'web.php')]

print("Searching for offline routes...")
for rf in route_files:
    if os.path.exists(rf):
        with open(rf, 'r', encoding='utf-8') as f:
            content = f.read()
            if 'offline' in content:
                print(f"Found 'offline' in {rf}")
            else:
                print(f"No 'offline' in {rf}")
