<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Optional, LOCAL-ONLY inventory for exercising the dealership admin.
 *
 * Records intentionally use ordinary display names, without preview badges.
 * They are illustrative and MUST NOT be imported as verified sale inventory.
 * Run via `php artisan carfolk:local-inventory`, not DatabaseSeeder.
 */
class LocalInventorySeeder extends Seeder
{
    public static function isAllowed(): bool
    {
        if (!app()->environment('local', 'testing')) {
            return false;
        }

        if (config('database.default') !== 'pgsql') {
            return false;
        }

        $connection = config('database.connections.pgsql');

        return is_array($connection)
            && empty($connection['url'])
            && in_array($connection['host'] ?? '', ['127.0.0.1', 'localhost'], true)
            && in_array($connection['database'] ?? '', ['carfolk', 'carfolk_test'], true);
    }

    /**
     * @return array{created:int,existing:int}
     */
    public function run(bool $publish = false): array
    {
        if (!self::isAllowed()) {
            throw new RuntimeException('Local inventory can only be created in a local/testing PostgreSQL database on localhost named carfolk (or carfolk_test). No changes made.');
        }

        $inventory = [
            [
                'stock_reference' => 'CF-1001',
                'make' => 'Toyota',
                'model' => 'Land Cruiser Prado',
                'trim' => 'TX-L',
                'year' => 2018,
                'price_kes' => 6950000,
                'mileage_km' => 78500,
                'engine_cc' => 2800,
                'fuel_type' => 'Diesel',
                'transmission' => 'Automatic',
                'drivetrain' => '4WD',
                'body_type' => 'SUV',
                'condition' => 'Foreign-used',
                'colour' => 'Pearl White',
                'location' => 'Nairobi',
                'description' => 'Spacious seven-seat SUV with four-wheel drive, automatic transmission and a practical cabin layout.',
                'features' => ['Seven seats', 'Reverse camera', 'Cruise control', 'Climate control'],
                'status' => 'available',
                'is_featured' => true,
            ],
            [
                'stock_reference' => 'CF-1002',
                'make' => 'Mazda',
                'model' => 'CX-5',
                'trim' => 'XD',
                'year' => 2021,
                'price_kes' => 3780000,
                'mileage_km' => 46500,
                'engine_cc' => 2200,
                'fuel_type' => 'Diesel',
                'transmission' => 'Automatic',
                'drivetrain' => 'AWD',
                'body_type' => 'SUV',
                'condition' => 'Foreign-used',
                'colour' => 'Soul Red',
                'location' => 'Nairobi',
                'description' => 'Mid-size crossover with a diesel engine, comfortable seating and an automatic gearbox.',
                'features' => ['Parking sensors', 'Reverse camera', 'Keyless entry', 'Bluetooth audio'],
                'status' => 'available',
                'is_featured' => true,
            ],
            [
                'stock_reference' => 'CF-1003',
                'make' => 'Toyota',
                'model' => 'RAV4',
                'trim' => 'Adventure',
                'year' => 2020,
                'price_kes' => 4250000,
                'mileage_km' => 62200,
                'engine_cc' => 2000,
                'fuel_type' => 'Petrol',
                'transmission' => 'Automatic',
                'drivetrain' => 'AWD',
                'body_type' => 'SUV',
                'condition' => 'Foreign-used',
                'colour' => 'Silver',
                'location' => 'Nairobi',
                'description' => 'Versatile five-seat SUV with a raised driving position, roomy boot and automatic transmission.',
                'features' => ['Roof rails', 'Cruise control', 'Reverse camera', 'USB connectivity'],
                'status' => 'available',
                'is_featured' => false,
            ],
            [
                'stock_reference' => 'CF-1004',
                'make' => 'Subaru',
                'model' => 'Forester',
                'trim' => 'Premium',
                'year' => 2019,
                'price_kes' => 3150000,
                'mileage_km' => 71200,
                'engine_cc' => 2500,
                'fuel_type' => 'Petrol',
                'transmission' => 'CVT',
                'drivetrain' => 'AWD',
                'body_type' => 'SUV',
                'condition' => 'Foreign-used',
                'colour' => 'Dark Grey',
                'location' => 'Nairobi',
                'description' => 'Practical family SUV with all-wheel drive and useful passenger and luggage space.',
                'features' => ['AWD', 'Parking camera', 'Climate control', 'Roof rails'],
                'status' => 'available',
                'is_featured' => false,
            ],
            [
                'stock_reference' => 'CF-1005',
                'make' => 'Honda',
                'model' => 'Fit',
                'trim' => 'Hybrid',
                'year' => 2020,
                'price_kes' => 1750000,
                'mileage_km' => 53800,
                'engine_cc' => 1500,
                'fuel_type' => 'Hybrid',
                'transmission' => 'CVT',
                'drivetrain' => '2WD',
                'body_type' => 'Hatchback',
                'condition' => 'Foreign-used',
                'colour' => 'Blue',
                'location' => 'Nairobi',
                'description' => 'Compact hybrid hatchback with flexible interior space and a city-friendly footprint.',
                'features' => ['Hybrid powertrain', 'Rear parking camera', 'Bluetooth audio', 'Folding rear seats'],
                'status' => 'available',
                'is_featured' => false,
            ],
        ];

        return DB::transaction(function () use ($inventory, $publish): array {
            $created = 0;
            $existing = 0;

            foreach ($inventory as $attributes) {
                $attributes['slug'] = Str::slug(
                    $attributes['make'].'-'.$attributes['model'].'-'.$attributes['year'].'-'.$attributes['stock_reference']
                );
                $attributes['is_published'] = $publish;

                $vehicle = Vehicle::firstOrCreate(
                    ['stock_reference' => $attributes['stock_reference']],
                    $attributes
                );

                if ($vehicle->wasRecentlyCreated) {
                    $created++;
                } else {
                    // Preserve every staff edit, including price, status, photos and publication.
                    $existing++;
                }
            }

            return compact('created', 'existing');
        });
    }
}
