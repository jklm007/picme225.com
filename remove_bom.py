import codecs

filepath = r'c:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\routes\web.php'
with open(filepath, 'r', encoding='utf-8-sig') as f:
    content = f.read()
with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)
print("BOM removed from web.php!")
