<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create system administrator for Water Billing Management System
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@dunsinane.lk',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123!@#'), // Strong default password
            'role' => 'admin',
            'is_active' => true,
        ]);

        echo "✅ System administrator created successfully!\n";
        echo "📧 Email: admin@dunsinane.lk\n";
        echo "🔐 Password: admin123!@#\n";
        echo "⚠️  Please change this password immediately after first login!\n";
        echo "🔗 Login at: http://127.0.0.1:8000/login\n";
    }
}
