import base64

with open("qrcode_base64.txt", "r") as f:
    data = f.read().strip()

# Remove data:image/png;base64, header if present
if data.startswith("data:image"):
    data = data.split(",")[1]

# Decode and write to png in the artifacts directory
artifact_path = r"C:\Users\HP\.gemini\antigravity\brain\fa9c1fab-9eae-4510-8fa2-7fc42c3b330a\qrcode.png"
with open(artifact_path, "wb") as f_png:
    f_png.write(base64.b64decode(data))

print("QR code PNG saved successfully to:", artifact_path)
