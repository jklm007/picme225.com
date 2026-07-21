import re

java_path = r"C:\Users\HP\Documents\Jews-world Backend\PickeMe.PRO_andoid\app\src\main\java\com\picmepro\app\Fragments\HomeFragment.java"

with open(java_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Add Variable declarations
if 'android.widget.CheckBox chkWithDriver;' not in content:
    content = content.replace(
        'EditText etSourceName', 
        'android.widget.CheckBox chkWithDriver;\n    android.widget.Button btnRentalStartDate, btnRentalEndDate;\n    String rentalStartDateStr = "";\n    String rentalEndDateStr = "";\n\n    EditText etSourceName'
    )

# 2. Fix URGENCE -> AMBULANCE
content = content.replace('FareCalculator.ServiceCategory.URGENCE', 'FareCalculator.ServiceCategory.AMBULANCE')

with open(java_path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Patch applied.")
