<?php

namespace Tests\Feature;

use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class SharedServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        return User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'payment_mode' => 'CASH',
            'email' => 'test@example.com',
            'mobile' => '0102030405',
            'password' => bcrypt('secret'),
            'device_type' => 'android',
            'login_by' => 'manual',
        ]);
    }

    private function createServiceType(): ServiceType
    {
        return ServiceType::create([
            'name' => 'Partage Test',
            'provider_name' => 'Provider',
            'image' => 'service/test.png',
            'price' => 100,
            'fixed' => 200,
            'description' => 'Partage',
            'status' => 1,
            'minute' => 1,
            'hour' => 0,
            'distance' => 10,
            'calculator' => 'DISTANCE',
            'capacity' => 4,
            'rental_amount' => 0,
            'ambulance' => 0,
            'day' => 0,
            'outstation_price' => 0,
            'sharing_type' => 'PDP',
            'free_km_per_passenger' => 2,
            'price_per_segment' => 50,
            'max_detour_communal' => 5,
            'max_detour_intercommunal' => 10,
        ]);
    }

    public function test_it_creates_shared_request(): void
    {
        $user = $this->createUser();
        $serviceType = $this->createServiceType();
        Passport::actingAs($user);

        $segments = [
            [
                's_latitude' => 5.33,
                's_longitude' => -4.02,
                'd_latitude' => 5.34,
                'd_longitude' => -4.05,
                's_address' => 'Point A',
                'd_address' => 'Point B',
                'price' => 100,
            ],
        ];

        $response = $this->postJson('/api/user/shared/request', [
            'service_type_id' => $serviceType->id,
            'payment_mode' => 'CASH',
            's_latitude' => 5.33,
            's_longitude' => -4.02,
            's_address' => 'Origine',
            'd_latitude' => 5.35,
            'd_longitude' => -4.04,
            'd_address' => 'Destination',
            'segments' => $segments,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'request_id',
                'fare' => ['fare_per_passenger'],
            ]);

        $this->assertDatabaseCount('user_requests', 1);
    }

    public function test_it_estimates_shared_fare(): void
    {
        $user = $this->createUser();
        $serviceType = $this->createServiceType();
        Passport::actingAs($user);

        $segments = [
            [
                's_latitude' => 5.30,
                's_longitude' => -4.00,
                'd_latitude' => 5.32,
                'd_longitude' => -4.03,
                's_address' => 'Stop 1',
                'd_address' => 'Stop 2',
            ],
        ];

        $response = $this->postJson('/api/user/shared/fare', [
            'service_type_id' => $serviceType->id,
            'segments' => $segments,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'fare' => [
                    'fare_per_passenger',
                    'distance_km',
                ],
                'segments',
                'max_detour_minutes',
                'max_stop_distance_km',
            ]);
    }
}

