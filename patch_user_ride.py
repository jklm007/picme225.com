import re

filepath = r"C:\Users\HP\Documents\Jews-world Backend\picme225.com_backend\app\Http\Controllers\UserRideController.php"

with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

old_block = """        } else {
            // Recherche standard
            $Providers->whereHas('service', function ($query) use ($service_type_id, $isLocation, $withDriver) {
                $query->where('status', 'active');
                $query->where('service_type_id', $service_type_id);
                if ($isLocation) {
                    if ($withDriver) {
                        $query->where('rental_driver_preference', '!=', 'WITHOUT_DRIVER');
                    } else {
                        $query->where('rental_driver_preference', '!=', 'WITH_DRIVER');
                    }
                }
            });
        }

        $variant = $this->_normalizeVariant($request->input('ride_variant', 'prive'));"""

new_block = """        } else {
            // Recherche standard
            $variant = $this->_normalizeVariant($request->input('ride_variant', 'prive'));
            $Providers->whereHas('service', function ($query) use ($service_type_id, $isLocation, $withDriver, $variant) {
                if ($variant === 'partage') {
                    $query->whereIn('status', ['active', 'riding']);
                } else {
                    $query->where('status', 'active');
                }
                $query->where('service_type_id', $service_type_id);
                if ($isLocation) {
                    if ($withDriver) {
                        $query->where('rental_driver_preference', '!=', 'WITHOUT_DRIVER');
                    } else {
                        $query->where('rental_driver_preference', '!=', 'WITH_DRIVER');
                    }
                }
            });
            
            if ($variant === 'partage') {
                $Providers->whereDoesntHave('trips', function($q) {
                    $q->whereIn('status', ['SEARCHING', 'ACCEPTED', 'STARTED', 'ARRIVED', 'PICKEDUP'])
                      ->where('ride_variant', '!=', 'partage');
                });
            }
        }

        $variant = $this->_normalizeVariant($request->input('ride_variant', 'prive'));"""

content = content.replace(old_block, new_block)

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Patched UserRideController.php for ride_variant == 'partage'")
