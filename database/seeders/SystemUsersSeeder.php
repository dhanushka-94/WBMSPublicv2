<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SystemUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates essential system users for AquaBill by olexto
     */
    public function run(): void
    {
        echo "🚀 Creating system users for AquaBill by olexto...\n";

        // 1. Create Main System Administrator
        $admin = User::firstOrCreate(
            ['email' => 'admin@aquabill.olexto.com'],
            [
                'name' => 'System Administrator',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
        echo "✅ Admin user created: {$admin->email}\n";

        // 2. Create Manager Users (3 managers)
        $managers = [
            ['name' => 'Operations Manager', 'email' => 'manager@aquabill.olexto.com'],
            ['name' => 'Billing Manager', 'email' => 'billing.manager@aquabill.olexto.com'],
            ['name' => 'Field Manager', 'email' => 'field.manager@aquabill.olexto.com'],
        ];

        foreach ($managers as $managerData) {
            $manager = User::firstOrCreate(
                ['email' => $managerData['email']],
                [
                    'name' => $managerData['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'role' => 'manager',
                ]
            );
            echo "✅ Manager user created: {$manager->email}\n";
        }

        // 3. Create Staff Users (3 staff members)
        $staff = [
            ['name' => 'Customer Service Staff', 'email' => 'staff@aquabill.olexto.com'],
            ['name' => 'Billing Staff', 'email' => 'billing.staff@aquabill.olexto.com'],
            ['name' => 'Support Staff', 'email' => 'support.staff@aquabill.olexto.com'],
        ];

        foreach ($staff as $staffData) {
            $staffUser = User::firstOrCreate(
                ['email' => $staffData['email']],
                [
                    'name' => $staffData['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'role' => 'staff',
                ]
            );
            echo "✅ Staff user created: {$staffUser->email}\n";
        }

        // 4. Create Meter Reader Users (5 meter readers)
        $meterReaders = [
            ['name' => 'Meter Reader 1', 'email' => 'reader1@aquabill.olexto.com'],
            ['name' => 'Meter Reader 2', 'email' => 'reader2@aquabill.olexto.com'],
            ['name' => 'Meter Reader 3', 'email' => 'reader3@aquabill.olexto.com'],
            ['name' => 'Meter Reader 4', 'email' => 'reader4@aquabill.olexto.com'],
            ['name' => 'Meter Reader 5', 'email' => 'reader5@aquabill.olexto.com'],
        ];

        foreach ($meterReaders as $readerData) {
            $reader = User::firstOrCreate(
                ['email' => $readerData['email']],
                [
                    'name' => $readerData['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'role' => 'meter_reader',
                ]
            );
            echo "✅ Meter reader created: {$reader->email}\n";
        }

        echo "\n🎉 System users created successfully!\n";
        echo "📋 Summary:\n";
        echo "   👑 Admin: 1 user\n";
        echo "   🏢 Managers: 3 users\n";
        echo "   👥 Staff: 3 users\n";
        echo "   📊 Meter Readers: 5 users\n";
        echo "   📱 Total: 12 system users\n";
        echo "\n🔑 Default Login Credentials:\n";
        echo "   📧 Email: admin@aquabill.olexto.com\n";
        echo "   🔐 Password: password\n";
        echo "   ⚠️  All users have the same password for initial setup\n";
        echo "\n🌐 Access the system at: http://127.0.0.1:8000/login\n";
    }
}