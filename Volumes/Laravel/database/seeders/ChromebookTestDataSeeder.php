<?php

namespace Database\Seeders;

use App\Models\ChromebookInventory;
use App\Models\ChromebookUsage;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ChromebookTestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create test inventory entries
        $testDevices = [
            ['serial_number' => 'TEST-SERIAL-001', 'asset_id' => 'ASSET-001'],
            ['serial_number' => 'TEST-SERIAL-002', 'asset_id' => 'ASSET-002'],
            ['serial_number' => 'TEST-SERIAL-003', 'asset_id' => 'ASSET-003'],
            ['serial_number' => 'TEST-SERIAL-004', 'asset_id' => null],
            ['serial_number' => 'TEST-SERIAL-005', 'asset_id' => 'ASSET-005'],
        ];

        foreach ($testDevices as $device) {
            ChromebookInventory::updateOrCreate(
                ['serial_number' => $device['serial_number']],
                ['asset_id' => $device['asset_id']]
            );
        }

        // Create test usage entries with various timestamps
        $testUsage = [
            // Device 1 - multiple users over time
            ['serial_number' => 'TEST-SERIAL-001', 'asset_id' => 'ASSET-001', 'user_email' => 'student1@test.edu', 'recorded_at' => Carbon::now()->subDays(1)],
            ['serial_number' => 'TEST-SERIAL-001', 'asset_id' => 'ASSET-001', 'user_email' => 'student2@test.edu', 'recorded_at' => Carbon::now()->subDays(3)],
            ['serial_number' => 'TEST-SERIAL-001', 'asset_id' => 'ASSET-001', 'user_email' => 'student3@test.edu', 'recorded_at' => Carbon::now()->subDays(7)],

            // Device 2 - single recent user
            ['serial_number' => 'TEST-SERIAL-002', 'asset_id' => 'ASSET-002', 'user_email' => 'teacher1@test.edu', 'recorded_at' => Carbon::now()->subHours(2)],

            // Device 3 - same student using multiple times
            ['serial_number' => 'TEST-SERIAL-003', 'asset_id' => 'ASSET-003', 'user_email' => 'student1@test.edu', 'recorded_at' => Carbon::now()->subHours(6)],

            // Device 4 - no asset ID
            ['serial_number' => 'TEST-SERIAL-004', 'asset_id' => null, 'user_email' => 'student4@test.edu', 'recorded_at' => Carbon::now()->subDays(2)],

            // Device 5 - multiple recent entries
            ['serial_number' => 'TEST-SERIAL-005', 'asset_id' => 'ASSET-005', 'user_email' => 'student5@test.edu', 'recorded_at' => Carbon::now()->subMinutes(30)],
            ['serial_number' => 'TEST-SERIAL-005', 'asset_id' => 'ASSET-005', 'user_email' => 'student6@test.edu', 'recorded_at' => Carbon::now()->subHours(12)],
            ['serial_number' => 'TEST-SERIAL-005', 'asset_id' => 'ASSET-005', 'user_email' => 'student7@test.edu', 'recorded_at' => Carbon::now()->subDays(1)],
        ];

        foreach ($testUsage as $usage) {
            ChromebookUsage::create($usage);
        }

        $this->command->info('Created ' . count($testDevices) . ' test inventory entries');
        $this->command->info('Created ' . count($testUsage) . ' test usage entries');
    }
}
