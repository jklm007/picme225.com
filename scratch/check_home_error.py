import paramiko

client=paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

cmd = "kubectl exec laravel-deployment-b5fb86954-cgldw -- curl -s -k -H 'Accept: text/html' http://localhost:8000/"
_, out, err = client.exec_command(cmd)
html = out.read().decode('utf-8', errors='replace')
print("Response size:", len(html))

if "Exception" in html or "Error" in html or "Traceback" in html:
    # Try to find the title of the error
    import re
    title_match = re.search(r'<title>(.*?)</title>', html, re.IGNORECASE)
    if title_match:
        print("Title:", title_match.group(1))
    
    # Try to find the specific error message
    exc_match = re.search(r'class="exception_title">\s*(.*?)\s*</', html, re.IGNORECASE | re.DOTALL)
    if exc_match:
        print("Exception:", exc_match.group(1).strip())
        
    msg_match = re.search(r'class="exception_message">\s*(.*?)\s*</', html, re.IGNORECASE | re.DOTALL)
    if msg_match:
        print("Message:", msg_match.group(1).strip())
        
    # Also check if it's a generic laravel error page without whoops
    if not exc_match:
        generic_match = re.search(r'<!-- Error: (.*?) -->', html)
        if generic_match:
            print("Generic Error:", generic_match.group(1))

    # Print the start of the body just in case
    body_start = html.find('<body>')
    if body_start != -1:
        print(html[body_start:body_start+500])
else:
    print(html[:500])
