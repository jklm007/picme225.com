import os

def search_text(dir_path, search_str):
    for root, dirs, files in os.walk(dir_path):
        if 'node_modules' in dirs: dirs.remove('node_modules')
        if 'vendor' in dirs: dirs.remove('vendor')
        if '.git' in dirs: dirs.remove('.git')
        for file in files:
            file_path = os.path.join(root, file)
            try:
                with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                    for i, line in enumerate(f, 1):
                        if search_str in line:
                            print(f"{file_path}:{i}: {line.strip()}")
            except Exception as e:
                pass

search_text(r'c:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\database\migrations', "pgsql")
