import requests
import json

url = "http://127.0.0.1:8010/api/user/gateway/sms-received"

payload = {
    "from": "WAVE",
    "message": "Vous avez recu 5000 FCFA de 0000000000. Transaction: WAVE12345"
}

headers = {'Content-Type': 'application/json'}

try:
    response = requests.post(url, headers=headers, data=json.dumps(payload))
    print(f"Status: {response.status_code}")
    print("Response text:")
    print(response.text)
except Exception as e:
    print(f"Error: {e}")
