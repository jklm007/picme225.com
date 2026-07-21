import re

xml_path = r"C:\Users\HP\Documents\Jews-world Backend\PickeMe.PRO_andoid\app\src\main\res\layout\fragment_home.xml"
java_path = r"C:\Users\HP\Documents\Jews-world Backend\PickeMe.PRO_andoid\app\src\main\java\com\picmepro\app\Fragments\HomeFragment.java"

# 1. Patch XML
with open(xml_path, 'r', encoding='utf-8') as f:
    xml_content = f.read()

hospital_xml = """            </LinearLayout>

            <LinearLayout
                android:id="@+id/llHospitalSelection"
                android:layout_width="match_parent"
                android:layout_height="wrap_content"
                android:orientation="vertical"
                android:visibility="gone"
                android:layout_marginTop="8dp">
                <TextView
                    android:layout_width="wrap_content"
                    android:layout_height="wrap_content"
                    android:text="SǸlectionnez un hpital partenaire"
                    android:textColor="@color/black"
                    android:textSize="14sp"
                    android:textStyle="bold"
                    android:layout_marginBottom="4dp"/>
                <Spinner
                    android:id="@+id/hospital_spinner"
                    android:layout_width="match_parent"
                    android:layout_height="45dp"
                    android:background="@drawable/border_square" />
            </LinearLayout>"""

if 'android:id="@+id/llHospitalSelection"' not in xml_content:
    xml_content = xml_content.replace('</LinearLayout>\n\n            \n            <com.picmepro.app.Utils.MyBoldTextView\n                android:id="@+id/tvDepannageHint"', hospital_xml + '\n\n            <com.picmepro.app.Utils.MyBoldTextView\n                android:id="@+id/tvDepannageHint"')
    
    with open(xml_path, 'w', encoding='utf-8') as f:
        f.write(xml_content)
    print("XML patched!")
else:
    print("XML already patched.")

# 2. Patch Java
with open(java_path, 'r', encoding='utf-8') as f:
    java_content = f.read()

# Add imports for DatePicker and TimePicker
if 'import android.app.DatePickerDialog;' not in java_content:
    java_content = java_content.replace('import android.app.Dialog;', 'import android.app.Dialog;\nimport android.app.DatePickerDialog;\nimport android.app.TimePickerDialog;')

# Add variables
if 'CheckBox chkWithDriver;' not in java_content:
    java_content = java_content.replace('Button getPricing;', 'Button getPricing;\n    CheckBox chkWithDriver;\n    Button btnRentalStartDate, btnRentalEndDate;\n    String rentalStartDateStr = "";\n    String rentalEndDateStr = "";')

# Bind variables
if 'chkWithDriver = rootView.findViewById' not in java_content:
    bind_code = """
        chkWithDriver = rootView.findViewById(R.id.chkWithDriver);
        btnRentalStartDate = rootView.findViewById(R.id.btnRentalStartDate);
        btnRentalEndDate = rootView.findViewById(R.id.btnRentalEndDate);
        llHospitalSelection = rootView.findViewById(R.id.llHospitalSelection);
        
        if (btnRentalStartDate != null) {
            btnRentalStartDate.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    showDateTimePicker(true);
                }
            });
        }
        if (btnRentalEndDate != null) {
            btnRentalEndDate.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    showDateTimePicker(false);
                }
            });
        }
"""
    java_content = java_content.replace('llVoyageSelection = rootView.findViewById(R.id.llVoyageSelection);', 'llVoyageSelection = rootView.findViewById(R.id.llVoyageSelection);\n' + bind_code)

# Add showDateTimePicker method
if 'private void showDateTimePicker' not in java_content:
    dtp_code = """
    private void showDateTimePicker(final boolean isStart) {
        final java.util.Calendar currentDate = java.util.Calendar.getInstance();
        new DatePickerDialog(getContext(), new DatePickerDialog.OnDateSetListener() {
            @Override
            public void onDateSet(android.widget.DatePicker view, int year, int monthOfYear, int dayOfMonth) {
                final java.util.Calendar date = java.util.Calendar.getInstance();
                date.set(year, monthOfYear, dayOfMonth);
                new TimePickerDialog(getContext(), new TimePickerDialog.OnTimeSetListener() {
                    @Override
                    public void onTimeSet(android.widget.TimePicker view, int hourOfDay, int minute) {
                        date.set(java.util.Calendar.HOUR_OF_DAY, hourOfDay);
                        date.set(java.util.Calendar.MINUTE, minute);
                        java.text.SimpleDateFormat sdf = new java.text.SimpleDateFormat("yyyy-MM-dd HH:mm:00", java.util.Locale.getDefault());
                        String formattedDate = sdf.format(date.getTime());
                        if (isStart) {
                            rentalStartDateStr = formattedDate;
                            if (btnRentalStartDate != null) btnRentalStartDate.setText(formattedDate);
                        } else {
                            rentalEndDateStr = formattedDate;
                            if (btnRentalEndDate != null) btnRentalEndDate.setText(formattedDate);
                        }
                    }
                }, currentDate.get(java.util.Calendar.HOUR_OF_DAY), currentDate.get(java.util.Calendar.MINUTE), true).show();
            }
        }, currentDate.get(java.util.Calendar.YEAR), currentDate.get(java.util.Calendar.MONTH), currentDate.get(java.util.Calendar.DATE)).show();
    }
"""
    java_content = java_content.replace('public void checkCategoryVisibility() {', dtp_code + '\n    public void checkCategoryVisibility() {')

# Toggle visibility for llHospitalSelection
java_content = java_content.replace('if (llHospitalSelection != null) llHospitalSelection.setVisibility(View.GONE);', '')
java_content = java_content.replace('llRouteAndPriceContainer.setVisibility(View.VISIBLE);', 'llRouteAndPriceContainer.setVisibility(View.VISIBLE);\n            if (llHospitalSelection != null) llHospitalSelection.setVisibility(category == FareCalculator.ServiceCategory.URGENCE ? View.VISIBLE : View.GONE);')


with open(java_path, 'w', encoding='utf-8') as f:
    f.write(java_content)
print("Java patched!")
