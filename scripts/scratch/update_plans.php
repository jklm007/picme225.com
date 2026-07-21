<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\SubscriptionPlan;

$plans = [
    'STANDARD' => ['max_categories' => 1, 'priority' => 10],
    'ECO' => ['max_categories' => 2, 'priority' => 50],
    'PRO' => ['max_categories' => 3, 'priority' => 80],
    'GOLD' => ['max_categories' => 10, 'priority' => 100],
];

foreach ($plans as $name => $data) {
    $plan = SubscriptionPlan::where('name', $name)->first();
    if ($plan) {
        echo "Updating Plan: $name\n";
        $plan->max_categories = $data['max_categories'];
        $plan->priority = $data['priority'];
        $plan->price = ($name == 'GOLD' ? 50000 : ($name == 'PRO' ? 10000 : ($name == 'ECO' ? 5000 : 0)));
        $plan->commission_type = ($name == 'GOLD' ? 'fixed' : 'percentage');
        $plan->commission_value = ($name == 'GOLD' ? 0 : ($name == 'PRO' ? 5 : ($name == 'ECO' ? 10 : 15)));
        $plan->staking_bonus_percentage = ($name == 'GOLD' ? 5.0 : 0);
        $plan->save();
        print_r($plan->toArray());
    } else {
        echo "Plan $name NOT found, creating...\n";
        SubscriptionPlan::create([
            'name' => $name,
            'price' => ($name == 'GOLD' ? 20000 : ($name == 'PRO' ? 10000 : ($name == 'ECO' ? 5000 : 0))),
            'max_categories' => $data['max_categories'],
            'priority' => $data['priority'],
            'status' => 'active',
            'commission_type' => ($name == 'GOLD' ? 'fixed' : 'percentage'),
            'commission_value' => ($name == 'GOLD' ? 50 : ($name == 'PRO' ? 5 : ($name == 'ECO' ? 10 : 15))),
        ]);
    }
}
