import os
import tarfile
import paramiko

files_to_deploy = [
    "app/Http/Controllers/InternalUploadController.php",
    "app/Http/Controllers/MarketplaceListingController.php",
    "app/Http/Controllers/Admin/MarketplaceListingController.php",
    "app/Http/Controllers/ProviderStoreController.php",
    "app/Http/Controllers/SecureChatController.php",
    "app/Http/Controllers/SocialTransportController.php",
    "app/Http/Controllers/UserMarketplaceController.php",
    "app/Http/Controllers/ProviderResources/TripController.php",
    "app/Http/Controllers/Resource/AdCampaignResource.php",
    "app/Http/Controllers/Resource/MainCategoryResource.php",
    "app/Http/Controllers/Resource/ServiceResource.php"
]

tar_name = "controllers_update.tar.gz"
with tarfile.open(tar_name, "w:gz") as tar:
    for f in files_to_deploy:
        if os.path.exists(f):
            tar.add(f)
            print(f"Added {f}")
        else:
            print(f"WARNING: {f} not found!")

print("Tar created.")

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('109.199.123.69', username='root', password='Charlotte23')

sftp = client.open_sftp()
sftp.put(tar_name, '/tmp/controllers_update.tar.gz')
sftp.close()
print("Uploaded to server.")

stdin, stdout, stderr = client.exec_command("kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}'")
pod = stdout.read().decode().strip().strip("'")

client.exec_command(f"kubectl cp /tmp/controllers_update.tar.gz {pod}:/tmp/controllers_update.tar.gz")
stdin, stdout, stderr = client.exec_command(f"kubectl exec {pod} -- tar -xzf /tmp/controllers_update.tar.gz -C /app")
print("Extracted in pod.")

print(stdout.read().decode())
print(stderr.read().decode())

client.close()
print("Deployment complete.")
