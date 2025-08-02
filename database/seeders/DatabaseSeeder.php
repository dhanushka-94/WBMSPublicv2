<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Creates only essential system users for production setup.
     */
    public function run(): void
    {
        echo "🚀 Starting AquaBill by olexto database seeding...\n";
        echo "📊 Creating essential system users only...\n";
        echo "\n";
        
        $this->call([
            SystemUsersSeeder::class,
        ]);
        
        echo "\n🎉 AquaBill by olexto seeding completed!\n";
        echo "🔑 Main Login credentials:\n";
        echo "   📧 Email: admin@aquabill.olexto.com\n";
        echo "   🔐 Password: password\n";
        echo "\n💡 Additional system accounts created:\n";
        echo "   🏢 Managers: manager@aquabill.olexto.com, billing.manager@aquabill.olexto.com, field.manager@aquabill.olexto.com\n";
        echo "   👥 Staff: staff@aquabill.olexto.com, billing.staff@aquabill.olexto.com, support.staff@aquabill.olexto.com\n";
        echo "   📊 Meter Readers: reader1@aquabill.olexto.com to reader5@aquabill.olexto.com\n";
        echo "   🔐 All accounts use password: password\n";
        echo "\n🌐 Access the system at: http://127.0.0.1:8000/login\n";
        echo "⚠️  Please change default passwords after first login!\n";
    }
}
