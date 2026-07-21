import requests
import json

# L'URL de votre backend local
url = "http://127.0.0.1:8010/api/gateway/sms-received"

print("========================================")
print("  SIMULATION RECEPTION SMS (WAVE/ORANGE)")
print("========================================")

phone = input("Entrez le numéro de téléphone du client (ex: 0707070707) : ")
amount = input("Entrez le montant (ex: 5000) : ")
tx_id = input("Entrez un ID de transaction fictif (ex: WAVE12345) : ")

# On simule un SMS de WAVE
sms_text = f"Vous avez recu {amount} FCFA de {phone}. Transaction: {tx_id}"

payload = {
    "from": "WAVE",
    "message": sms_text
}

headers = {
    'Content-Type': 'application/json'
}

print("\n[+] Envoi du SMS simulé au backend...")
print(f"Message : '{sms_text}'\n")

try:
    response = requests.post(url, headers=headers, data=json.dumps(payload))
    print(f"Status Code: {response.status_code}")
    print("Réponse du serveur :")
    print(json.dumps(response.json(), indent=4, ensure_ascii=False))
except Exception as e:
    print(f"Erreur de connexion : {e}")
