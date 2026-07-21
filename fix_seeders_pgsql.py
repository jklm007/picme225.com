import os

seeder_dir = "database/seeders"
for root, dirs, files in os.walk(seeder_dir):
    for f in files:
        if f.endswith(".php"):
            path = os.path.join(root, f)
            try:
                with open(path, "r", encoding="utf-8") as file:
                    content = file.read()
            except UnicodeDecodeError:
                try:
                    with open(path, "r", encoding="latin-1") as file:
                        content = file.read()
                except Exception as e:
                    print(f"Skipping {f}: {e}")
                    continue

            modified = False
            # Remplacement avec \DB pour éviter les soucis de namespace
            target_0 = "DB::statement('SET FOREIGN_KEY_CHECKS=0;');"
            replacement_0 = "if (\\DB::getDriverName() === 'mysql') { \\DB::statement('SET FOREIGN_KEY_CHECKS=0;'); } elseif (\\DB::getDriverName() === 'pgsql') { \\DB::statement(\"SET session_replication_role = 'replica';\"); }"
            
            if target_0 in content:
                content = content.replace(target_0, replacement_0)
                modified = True
            
            target_1 = "DB::statement('SET FOREIGN_KEY_CHECKS=1;');"
            replacement_1 = "if (\\DB::getDriverName() === 'mysql') { \\DB::statement('SET FOREIGN_KEY_CHECKS=1;'); } elseif (\\DB::getDriverName() === 'pgsql') { \\DB::statement(\"SET session_replication_role = 'origin';\"); }"
            
            if target_1 in content:
                content = content.replace(target_1, replacement_1)
                modified = True
                
            if modified:
                with open(path, "w", encoding="utf-8") as file:
                    file.write(content)
                print(f"Fixed {f}")
