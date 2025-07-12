<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * This will populate the system with comprehensive test data.
     */
    public function run(): void
    {
        echo "🚀 Starting comprehensive database seeding...\n";
        echo "⚠️  This will create 100+ records for each entity type.\n";
        echo "📊 Expected data counts:\n";
        echo "   - Users: 100+\n";
        echo "   - Divisions: 120+\n";
        echo "   - Customer Types: 15+\n";
        echo "   - Guarantors: 150+\n";
        echo "   - Rates: 100+\n";
        echo "   - Customers: 150+\n";
        echo "   - Water Meters: 150+\n";
        echo "   - Meter Readings: 500+\n";
        echo "   - Bills: 200+\n";
        echo "\n";
        
        $this->call([
            ComprehensiveSeeder::class,
        ]);
        
        echo "\n🎉 Comprehensive database seeding completed!\n";
        echo "🔑 Login credentials:\n";
        echo "   Email: admin@wassip.com\n";
        echo "   Password: password\n";
        echo "\n💡 Other test accounts:\n";
        echo "   Meter Readers: reader1@wassip.com to reader50@wassip.com (password: password)\n";
        echo "   Staff Members: staff1@wassip.com to staff30@wassip.com (password: password)\n";
        echo "   Managers: manager1@wassip.com to manager20@wassip.com (password: password)\n";
        echo "\n🌐 Access the system at: http://127.0.0.1:8000\n";
    }
}
