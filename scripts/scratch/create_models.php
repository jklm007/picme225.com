<?php

$dir = 'app/Models/';
if (!is_dir($dir)) mkdir($dir, 0777, true);

$template = '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class %s extends Model
{
    protected $table = \'%s\';
    protected $guarded = [];

    public function listing()
    {
        return $this->morphOne(\App\MarketplaceListing::class, \'listable\');
    }
}
';

file_put_contents($dir . 'MktRealEstate.php', sprintf($template, 'MktRealEstate', 'mkt_real_estates'));
file_put_contents($dir . 'MktVehicle.php', sprintf($template, 'MktVehicle', 'mkt_vehicles'));
file_put_contents($dir . 'MktLogistic.php', sprintf($template, 'MktLogistic', 'mkt_logistics'));
file_put_contents($dir . 'MktEvent.php', sprintf($template, 'MktEvent', 'mkt_events'));
file_put_contents($dir . 'MktService.php', sprintf($template, 'MktService', 'mkt_services'));
file_put_contents($dir . 'MktProduct.php', sprintf($template, 'MktProduct', 'mkt_products'));

echo "Models created successfully.\n";
