import os

def search_dir(dir_path, search_str):
    for root, dirs, files in os.walk(dir_path):
        if 'node_modules' in dirs: dirs.remove('node_modules')
        if 'build' in dirs: dirs.remove('build')
        for file in files:
            if file.endswith('.java') or file.endswith('.xml'):
                try:
                    with open(os.path.join(root, file), 'r', encoding='utf-8') as f:
                        lines = f.readlines()
                        for i, line in enumerate(lines):
                            if search_str.lower() in line.lower():
                                print(f"{os.path.join(root, file)}:{i+1}: {line.strip()}")
                except Exception as e:
                    pass

print("Searching for 'avec_chauffeur':")
search_dir(r'c:\Users\HP\Documents\Jews-world Backend\PickeMe.PRO_andoid', 'avec_chauffeur')

print("\nSearching for 'sans_chauffeur':")
search_dir(r'c:\Users\HP\Documents\Jews-world Backend\PickeMe.PRO_andoid', 'sans_chauffeur')
