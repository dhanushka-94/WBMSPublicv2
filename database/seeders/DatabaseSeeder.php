<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * This will create ONLY the system administrator account.
     * All other data has been removed for a clean system start.
     */
    public function run(): void
    {
        // Create ONLY the system administrator - all other data removed
        $this->call([
            AdminUserSeeder::class,
        ]);
        
        echo "\n🎉 Database completely flushed and clean!\n";
        echo "📋 Only essential admin account created:\n";
        echo "   ✅ System administrator user ONLY\n";
        echo "   ❌ All other data removed (customers, meters, bills, etc.)\n";
        echo "\n💡 Next steps:\n";
        echo "   1. Login at: http://127.0.0.1:8000/login\n";
        echo "   2. Change the default admin password immediately\n";
        echo "   3. Start fresh with your real data\n";
        echo "   4. Add divisions, customer types, and other data as needed\n";
    }
}
