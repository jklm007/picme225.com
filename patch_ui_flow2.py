import re
import os

xml_path = r"C:\Users\HP\Documents\Jews-world Backend\PickeMe.PRO_andoid\app\src\main\res\layout\fragment_home.xml"
java_path = r"C:\Users\HP\Documents\Jews-world Backend\PickeMe.PRO_andoid\app\src\main\java\com\picmepro\app\Fragments\HomeFragment.java"

with open(xml_path, 'r', encoding='utf-8') as f:
    xml_content = f.read()

# Helper to extract a block
def extract_block(text, start_tag):
    start_idx = text.find(start_tag)
    if start_idx == -1:
        return text, ""
    
    end_idx = start_idx
    open_count = 0
    in_block = False
    
    while end_idx < len(text):
        if text[end_idx:end_idx+13] == '<LinearLayout':
            open_count += 1
            in_block = True
        elif text[end_idx:end_idx+14] == '</LinearLayout>':
            open_count -= 1
        
        if in_block and open_count == 0:
            end_idx += 14
            break
        end_idx += 1
        
    block = text[start_idx:end_idx]
    text = text[:start_idx] + text[end_idx:]
    return text, block

blocks_to_move = [
    '<LinearLayout\n            android:id="@+id/lnrVariantSelectionContainer"',
    '<LinearLayout\n            android:id="@+id/lnrAmbulanceOptions"',
    '<LinearLayout\n            android:id="@+id/llRentalSelection"',
    '<LinearLayout\n            android:id="@+id/llVoyageSelection"',
    '<LinearLayout\n            android:id="@+id/lnrOutstationOptions"'
]

extracted_blocks = []
for start_tag in blocks_to_move:
    xml_content, block = extract_block(xml_content, start_tag)
    if block:
        extracted_blocks.append(block)

# Insert after <View android:layout_width="match_parent" android:layout_height="1dp" android:background="#EEEEEE" android:layout_marginBottom="12dp"/>
# This is right after tvSummaryDest inside llRouteAndPriceContainer
insert_marker = '<View android:layout_width="match_parent" android:layout_height="1dp" android:background="#EEEEEE" android:layout_marginBottom="12dp"/>'
insert_idx = xml_content.find(insert_marker)
if insert_idx != -1:
    insert_idx += len(insert_marker)
    combined_blocks = "\n\n        <!-- DYNAMIC OPTIONS MOVED HERE -->\n" + "\n".join(extracted_blocks) + "\n        <!-- END DYNAMIC OPTIONS -->\n"
    xml_content = xml_content[:insert_idx] + combined_blocks + xml_content[insert_idx:]
    print("Successfully moved blocks in XML.")
else:
    print("Could not find insert marker in XML.")

with open(xml_path, 'w', encoding='utf-8') as f:
    f.write(xml_content)


with open(java_path, 'r', encoding='utf-8') as f:
    java_content = f.read()

java_insert = """
        llRentalSelection = rootView.findViewById(R.id.llRentalSelection);
        llVoyageSelection = rootView.findViewById(R.id.llVoyageSelection);
        llHospitalSelection = rootView.findViewById(R.id.llHospitalSelection);
"""

if "llRentalSelection =" not in java_content:
    java_content = java_content.replace(
        "lnrAmbulanceOptions = rootView.findViewById(R.id.lnrAmbulanceOptions);",
        "lnrAmbulanceOptions = rootView.findViewById(R.id.lnrAmbulanceOptions);\n" + java_insert
    )
    print("Added findViewById to Java.")

with open(java_path, 'w', encoding='utf-8') as f:
    f.write(java_content)

print("Done.")
