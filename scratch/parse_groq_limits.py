import re
from bs4 import BeautifulSoup

with open(r"C:\Users\HP\.gemini\antigravity\brain\fa9c1fab-9eae-4510-8fa2-7fc42c3b330a\.system_generated\steps\16670\content.md", "r", encoding="utf-8") as f:
    html = f.read()

soup = BeautifulSoup(html, "html.parser")
text = soup.get_text(separator="\n", strip=True)

# Find sections mentioning rate limits or limits
lines = text.split("\n")
for i, line in enumerate(lines):
    if "Rate Limits" in line or "Tokens per Minute" in line or "Requests per Minute" in line:
        print("\n".join(lines[max(0, i-5):min(len(lines), i+20)]))
        break
