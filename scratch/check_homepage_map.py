import requests
import re

try:
    r = requests.get('https://picme225.site/')
    html = r.text
    if 'leaflet' in html.lower() or 'osm' in html.lower() or 'user.dashboard' in html:
        print("SUCCESS! The homepage has the map (user dashboard).")
    else:
        print("FAILURE! Still showing Ola Cabs or something else.")
    
    # Let's print a small snippet to see what's there
    title_match = re.search(r'<title>(.*?)</title>', html, re.IGNORECASE)
    if title_match:
        print(f"Title: {title_match.group(1)}")
    
    print(f"Page size: {len(html)} bytes")
except Exception as e:
    print(f"Error: {e}")
