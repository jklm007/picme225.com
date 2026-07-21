import requests

url = "http://109.199.123.69:8080/message/sendText/picme_whatsapp"
headers = {
    "apikey": "picme225-evolution-secret-key",
    "Content-Type": "application/json"
}
payload = {
    "number": "22558286571@s.whatsapp.net",
    "options": {
        "delay": 1200,
        "presence": "composing"
    },
    "text": "Test message pour voir si l'API peut vous écrire"
}

response = requests.post(url, json=payload, headers=headers)
print("Status Code:", response.status_code)
print("Response Body:", response.text)
