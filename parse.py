import sys, json;
for line in sys.stdin:
    if "production.ERROR" in line:
        try:
            prefix, json_str = line.split("{", 1)
            json_str = "{" + json_str
            data = json.loads(json_str)
            print("ERROR MESSAGE:", data.get("message", "No message"))
            if "exception" in data:
                print("EXCEPTION:", data["exception"])
        except Exception as e:
            print("Failed to parse:", line, e)