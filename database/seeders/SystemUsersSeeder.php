<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'System Administrator', 'email' => 'admin@aquabill.olexto.com', 'role' => 'admin'],
            ['name' => 'Operations Manager', 'email' => 'manager@aquabill.olexto.com', 'role' => 'manager'],
            ['name' => 'Billing Manager', 'email' => 'billing.manager@aquabill.olexto.com', 'role' => 'manager'],
            ['name' => 'Field Manager', 'email' => 'field.manager@aquabill.olexto.com', 'role' => 'manager'],
            ['name' => 'Billing Officer', 'email' => 'billing.officer@aquabill.olexto.com', 'role' => 'billing_officer'],
            ['name' => 'Cashier', 'email' => 'cashier@aquabill.olexto.com', 'role' => 'cashier'],
            ['name' => 'Customer Service Staff', 'email' => 'staff@aquabill.olexto.com', 'role' => 'staff'],
            ['name' => 'Billing Staff', 'email' => 'billing.staff@aquabill.olexto.com', 'role' => 'staff'],
            ['name' => 'Support Staff', 'email' => 'support.staff@aquabill.olexto.com', 'role' => 'staff'],
            ['name' => 'Meter Reader 1', 'email' => 'reader1@aquabill.olexto.com', 'role' => 'meter_reader'],
            ['name' => 'Meter Reader 2', 'email' => 'reader2@aquabill.olexto.com', 'role' => 'meter_reader'],
            ['name' => 'Meter Reader 3', 'email' => 'reader3@aquabill.olexto.com', 'role' => 'meter_reader'],
            ['name' => 'Meter Reader 4', 'email' => 'reader4@aquabill.olexto.com', 'role' => 'meter_reader'],
            ['name' => 'Meter Reader 5', 'email' => 'reader5@aquabill.olexto.com', 'role' => 'meter_reader'],
        ];

        foreach ($users as $user) {
            $account = User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                    'role' => $user['role'],
                    'is_active' => true,
                ]
            );
            $account->forceFill(['email_verified_at' => now()])->save();
        }
    }
}
