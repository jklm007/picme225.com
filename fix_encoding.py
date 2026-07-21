import re

MOJIBAKE = [
    ("Ã©",  "é"), ("Ã¨",  "è"), ("Ãª",  "ê"), ("Ã«",  "ë"),
    ("Ã ",  "à"), ("Ã¢",  "â"), ("Ã¤",  "ä"),
    ("Ã®",  "î"), ("Ã¯",  "ï"),
    ("Ã´",  "ô"), ("Ã¶",  "ö"),
    ("Ã»",  "û"), ("Ã¼",  "ü"),
    ("Ã§",  "ç"), ("Ã±",  "ñ"),
    ("Ã‰",  "É"), ("Ã‡",  "Ç"), ("Ãˆ",  "È"), ("ÃŠ",  "Ê"),
    ("Ã€",  "À"), ("Ã‚",  "Â"),
    # Emojis
    ("â ï¸", "\u26a0\ufe0f"),
    # Sequences Ǹ -> é (caractere latin mal encodé)
    ("ItinÃ©raire", "Itinéraire"),
    ("pÃ©ages",     "péages"),
    ("PartagǸ",     "Partagé"),
    ("itinǸraire",  "itinéraire"),
    ("ItinǸraire",  "Itinéraire"),
    ("SǸlectionnez","Sélectionnez"),
    ("sǸlectionn",  "sélectionn"),
    ("VǸrification","Vérification"),
    ("dǸfinir",     "définir"),
    ("CatǸgorie",   "Catégorie"),
    ("catǸgorie",   "catégorie"),
    ("rǸseau",      "réseau"),
    ("PrivǸ",       "Privé"),
    ("privǸ",       "privé"),
    ("DǸbut",       "Début"),
    ("dǸbut",       "début"),
    ("gǸoloc",      "géoloc"),
    ("GǸo",         "Géo"),
    ("sǸquestre",   "séquestre"),
    ("SǸquestre",   "Séquestre"),
    ("sǸcurit",     "sécurit"),
    ("rǸcup",       "récup"),
    ("RǸcup",       "Récup"),
    ("prǸcis",      "précis"),
    ("rǸponse",     "réponse"),
    ("complǸ",      "complé"),
    ("gǸnǸr",       "généra"),
    ("LǸgende",     "Légende"),
    ("rǸserv",      "réserv"),
    ("lǸgende",     "légende"),
    ("sǸlect",      "sélect"),
    ("accǸs",       "accès"),
    ("AccǸs",       "Accès"),
    ("ErrǸur",      "Erreur"),
    ("errǸur",      "erreur"),
    ("ǸvǸn",        "évén"),
    ("prǸ",         "pré"),
    ("PrǸ",         "Pré"),
    ("SǸ",          "Sé"),
    ("sǸ",          "sé"),
    ("rǸ",          "ré"),
    ("RǸ",          "Ré"),
    ("gǸ",          "gé"),
    ("GǸ",          "Gé"),
    ("tǸl",         "tél"),
    ("TǸl",         "Tél"),
    ("spǸ",         "spé"),
    ("crǸ",         "cré"),
    ("CrǸ",         "Cré"),
    ("clǸ",         "clé"),
    ("ClǸ",         "Clé"),
    ("brǸ",         "bré"),
    ("fǸv",         "fév"),
    ("FǸv",         "Fév"),
    ("prǸsence",    "présence"),
    ("Ǹ",           "é"),  # Fallback général
    ("hpital",      "hôpital"),
    ("Priv\ufffd",  "Privé"),
]

def fix_encoding(text):
    for bad, good in MOJIBAKE:
        text = text.replace(bad, good)
    # regex pour patterns Ã? restants
    def replace_a_tilde(m):
        raw = m.group(0)
        try:
            return raw.encode('latin-1').decode('utf-8')
        except Exception:
            return raw
    text = re.sub(r'Ã[^\s]{1}', replace_a_tilde, text)
    return text

files = [
    r"C:\Users\HP\Documents\Jews-world Backend\PickeMe.PRO_andoid\app\src\main\java\com\picmepro\app\Fragments\HomeFragment.java",
    r"C:\Users\HP\Documents\Jews-world Backend\PickeMe.PRO_andoid\app\src\main\res\layout\fragment_home.xml",
]

for path in files:
    try:
        with open(path, 'r', encoding='utf-8', errors='replace') as f:
            content = f.read()
        fixed = fix_encoding(content)
        if fixed != content:
            with open(path, 'w', encoding='utf-8') as f:
                f.write(fixed)
            print("Corrige: " + path.split("\\")[-1])
        else:
            print("Inchange: " + path.split("\\")[-1])
    except Exception as e:
        print("Erreur: " + str(e))

print("Done.")
