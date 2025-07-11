<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create essential system data for the Water Billing Management System
        $this->call([
            AdminUserSeeder::class,
            DivisionSeeder::class,
            CustomerTypeSeeder::class,
            // GuarantorSeeder::class, // Removed - sample data not needed for production
            // Note: RateSeeder should be run separately when needed
            // CustomerSampleSeeder::class, // Removed - no longer needed for production
        ]);
        
        echo "\n🎉 System initialization complete!\n";
        echo "📋 Essential data has been seeded:\n";
        echo "   ✅ System administrator user\n";
        echo "   ✅ Divisions\n";
        echo "   ✅ Customer types\n";
        echo "\n💡 Next steps:\n";
        echo "   1. Login at: http://127.0.0.1:8000/login\n";
        echo "   2. Change the default admin password\n";
        echo "   3. Configure rate structure in Settings\n";
        echo "   4. Add guarantors as needed for customers\n";
        echo "   5. Start adding real customers\n";
    }
}
