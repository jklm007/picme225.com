with open(r"c:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\routes\web.php", 'r', encoding='utf-8') as f:
    for i, line in enumerate(f, 1):
        if 'offline' in line:
            print(f"Line {i}: {line.strip()}")
