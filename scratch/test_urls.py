import requests

urls = [
    "https://picme225.site/storage/listings/bfd42f89-8d7d-4b8f-bf2a-9f5cf8281d39.webp", # ID 51
    "https://picme225.site/storage/listings/89124ed9-44c8-4a1f-90e2-a657ac721108_1784072218.webp", # ID 243
]

for url in urls:
    try:
        r = requests.head(url, timeout=5)
        print(f"{url} -> {r.status_code}")
    except Exception as e:
        print(f"{url} -> Error: {e}")
