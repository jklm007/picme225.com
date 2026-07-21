import paramiko
import os
import boto3
from botocore.config import Config

# === CONFIG R2 ===
R2_ACCESS_KEY = 'ab9da087a81d703a47f95e34a0167a27'
R2_SECRET_KEY = 'f3f4f9c1a0aee212d38db9d4cb412b35e776e622289f36b1bfc508f0607cf123'
R2_ENDPOINT = 'https://45dae7ec0d11d6baef63481feb03aa7d.r2.cloudflarestorage.com'
R2_BUCKET = 'picme225-storage'
CDN_BASE = 'https://media.picme225.site'

# Images to upload and their corresponding service names in DB
# Format: { 'r2_key': { 'local_path': '...', 'db_services': ['name1', ...], 'db_types': ['name1', ...] } }
IMAGE_MAPPINGS = {
    # Service categories
    'service/taxi.png': {'local': 'public/asset/img/cars/vtc.png', 'content_type': 'image/png'},
    'service/eco_partage.png': {'local': 'public/asset/img/cars/sedan.png', 'content_type': 'image/png'},
    'service/livraison.png': {'local': 'public/asset/img/cars/pickup.png', 'content_type': 'image/png'},
    'service/location.png': {'local': 'public/asset/img/cars/suv.png', 'content_type': 'image/png'},
    'service/urgence.png': {'local': 'public/asset/img/cars/van.png', 'content_type': 'image/png'},
    'service/voyage.png': {'local': 'public/asset/img/cars/minibus.png', 'content_type': 'image/png'},
    # ServiceType images
    'service/taxi_vtc.webp': {'local': 'public/asset/img/cars/vtc.png', 'content_type': 'image/png'},
    'service/van.webp': {'local': 'public/asset/img/cars/van.png', 'content_type': 'image/png'},
    'service/woro-woro.webp': {'local': 'public/asset/img/cars/woro.png', 'content_type': 'image/png'},
    'service/ambulance.webp': {'local': 'public/asset/img/cars/van.png', 'content_type': 'image/png'},
    'service/cargo.webp': {'local': 'public/asset/img/cars/pickup.png', 'content_type': 'image/png'},
    'service/inter-communal.webp': {'local': 'public/asset/img/cars/bus.png', 'content_type': 'image/png'},
    'service/moto.webp': {'local': 'public/asset/img/cars/moto.png', 'content_type': 'image/png'},
    'service/suv.webp': {'local': 'public/asset/img/cars/suv.png', 'content_type': 'image/png'},
    'service/berline.webp': {'local': 'public/asset/img/cars/limo.png', 'content_type': 'image/png'},
    'service/berline_voyage.webp': {'local': 'public/asset/img/cars/limo.png', 'content_type': 'image/png'},
    'service/suv_voyage.webp': {'local': 'public/asset/img/cars/suv.png', 'content_type': 'image/png'},
    'service/taxi_compteur.webp': {'local': 'public/asset/img/cars/taxicompteur.png', 'content_type': 'image/png'},
}

def upload_to_r2():
    print("=== Connexion a Cloudflare R2 ===")
    s3 = boto3.client(
        's3',
        endpoint_url=R2_ENDPOINT,
        aws_access_key_id=R2_ACCESS_KEY,
        aws_secret_access_key=R2_SECRET_KEY,
        config=Config(signature_version='s3v4'),
        region_name='auto'
    )
    
    for r2_key, info in IMAGE_MAPPINGS.items():
        local_path = info['local']
        content_type = info.get('content_type', 'image/png')
        
        if not os.path.exists(local_path):
            print(f"  SKIP (not found locally): {local_path}")
            continue
        
        print(f"  Uploading {local_path} -> s3://{R2_BUCKET}/{r2_key}")
        try:
            s3.upload_file(
                local_path,
                R2_BUCKET,
                r2_key,
                ExtraArgs={'ContentType': content_type, 'CacheControl': 'public, max-age=86400'}
            )
            print(f"  OK: {CDN_BASE}/{r2_key}")
        except Exception as e:
            print(f"  ERROR: {e}")
    
    print("\n=== Upload termine ! ===")

if __name__ == '__main__':
    upload_to_r2()
