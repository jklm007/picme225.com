import re

java_path = r"C:\Users\HP\Documents\Jews-world Backend\PickeMe.PRO_andoid\app\src\main\java\com\picmepro\app\Fragments\HomeFragment.java"

with open(java_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = 'requestParams.put("payment_mode", pm.equalsIgnoreCase("WALLET") ? "CASH" : pm);'
injection = """
        // ================= AJOUT SERVICE LOCATION ET URGENCE =================
        if (FareCalculator.getServiceCategory(selectedServiceName) == FareCalculator.ServiceCategory.RENTAL) {
            requestParams.put("with_driver", chkWithDriver != null && chkWithDriver.isChecked() ? "1" : "0");
            if (rentalStartDateStr != null && !rentalStartDateStr.isEmpty()) {
                requestParams.put("rental_start_date", rentalStartDateStr);
            }
            if (rentalEndDateStr != null && !rentalEndDateStr.isEmpty()) {
                requestParams.put("rental_end_date", rentalEndDateStr);
            }
        } else if (FareCalculator.getServiceCategory(selectedServiceName) == FareCalculator.ServiceCategory.URGENCE) {
            if (hospitalSpinner != null && hospitalSpinner.getSelectedItem() != null) {
                Hospital h = (Hospital) hospitalSpinner.getSelectedItem();
                requestParams.put("hospital_id", String.valueOf(h.getId()));
            }
        }
        // ====================================================================
"""

if "AJOUT SERVICE LOCATION ET URGENCE" not in content:
    content = content.replace(target, target + "\n" + injection)
    with open(java_path, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Patched sendRequest params successfully!")
else:
    print("Already patched.")
