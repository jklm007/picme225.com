import urllib.request
import urllib.error

url = "https://media.picme225.site/listings/bf4a181f-bbc5-4961-9781-6452ac53ac4e_1784106766.webp"
try:
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    response = urllib.request.urlopen(req)
    print("STATUS:", response.status)
    print("Content-Length:", response.getheader('Content-Length'))
except urllib.error.HTTPError as e:
    print("HTTP ERROR:", e.code)
    print(e.read().decode('utf-8', errors='ignore'))
except urllib.error.URLError as e:
    print("URL ERROR:", e.reason)
