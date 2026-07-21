with open(r"c:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\app\Helper\ViewHelper.php", 'r', encoding='utf-8') as f:
    lines = f.readlines()

found = False
for idx, line in enumerate(lines):
    if 'function get_osrm_routing' in line:
        found = True
        start = idx
        break

if found:
    for i in range(start, min(start + 50, len(lines))):
        print(f"{i+1}: {lines[i]}", end='')
else:
    print("Function not found in ViewHelper.php")
