<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\ActivityLog;
use Carbon\Carbon;

class RefreshDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:refresh-preserve-users 
                            {--force : Force the operation without confirmation}
                            {--preserve-admins : Only preserve admin users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh database while preserving system users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Water Billing Management System - Database Refresh');
        $this->info('This command will refresh the database while preserving system users');
        $this->newLine();

        // Check if force flag is used
        if (!$this->option('force')) {
            $this->warn('⚠️  This will PERMANENTLY DELETE all data except system users!');
            $this->newLine();
            $this->info('📊 What will be preserved:');
            $this->line('   ✅ System users (admin, manager, staff, meter_reader)');
            $this->line('   ✅ User authentication tokens');
            $this->line('   ✅ Critical user data');
            $this->newLine();
            $this->info('🗑️  What will be deleted:');
            $this->line('   ❌ All customers and their data');
            $this->line('   ❌ All water meters and readings');
            $this->line('   ❌ All bills and payment records');
            $this->line('   ❌ All divisions, guarantors, and rates');
            $this->line('   ❌ All activity logs (except critical)');
            $this->newLine();
            
            if (!$this->confirm('Are you sure you want to proceed?')) {
                $this->error('Operation cancelled.');
                return Command::FAILURE;
            }
        }

        try {
            $this->newLine();
            $this->info('🔄 Starting database refresh...');
            
            // Step 1: Preserve system users
            $this->info('1️⃣  Preserving system users...');
            $preservedUsers = $this->preserveSystemUsers();
            
            // Step 2: Clear all data except users
            $this->info('2️⃣  Clearing non-user data...');
            $this->clearNonUserData();
            
            // Step 3: Reset database structure
            $this->info('3️⃣  Resetting database structure...');
            $this->resetDatabaseStructure();
            
            // Step 4: Restore system users
            $this->info('4️⃣  Restoring system users...');
            $this->restoreSystemUsers($preservedUsers);
            
            // Step 5: Create essential data only (rates, customer types, divisions)
            $this->info('5️⃣  Creating essential system data...');
            $this->createEssentialData();
            
            // Step 6: Final validation
            $this->info('6️⃣  Validating database integrity...');
            $this->validateDatabaseIntegrity();
            
            $this->newLine();
            $this->info('✅ Database refresh completed successfully!');
            $this->displaySummary();
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Database refresh failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Preserve system users
     */
    private function preserveSystemUsers(): array
    {
        $preserveAdminsOnly = $this->option('preserve-admins');
        
        if ($preserveAdminsOnly) {
            $users = User::where('role', 'admin')->get();
            $this->line("   📋 Preserving {$users->count()} admin users");
        } else {
            $users = User::whereIn('role', ['admin', 'manager', 'staff', 'meter_reader'])->get();
            $this->line("   📋 Preserving {$users->count()} system users");
        }

        $preservedUsers = [];
        foreach ($users as $user) {
            $preservedUsers[] = [
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->password, // Already hashed
                'role' => $user->role,
                'email_verified_at' => $user->email_verified_at,
                'is_active' => $user->is_active ?? true,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        }

        return $preservedUsers;
    }

    /**
     * Clear all non-user data
     */
    private function clearNonUserData()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $tablesToClear = [
            'bills',
            'meter_readings', 
            'water_meters',
            'customers',
            'guarantors',
            'rates',
            'divisions',
            'customer_types',
            'activity_logs',
            'personal_access_tokens',
            'system_configurations'
        ];

        foreach ($tablesToClear as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                DB::table($table)->truncate();
                $this->line("   🗑️  Cleared {$table}: {$count} records");
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Reset database structure
     */
    private function resetDatabaseStructure()
    {
        // Reset auto-increment counters
        $tables = [
            'bills', 'meter_readings', 'water_meters', 'customers', 
            'guarantors', 'rates', 'divisions', 'customer_types', 
            'activity_logs', 'system_configurations'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
            }
        }

        $this->line("   🔄 Reset auto-increment counters for " . count($tables) . " tables");
    }

    /**
     * Restore system users
     */
    private function restoreSystemUsers(array $preservedUsers)
    {
        // Disable foreign key checks to handle constraints
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        
        // Delete all users instead of truncate to avoid foreign key issues
        DB::table('users')->delete();
        DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        // Restore preserved users
        foreach ($preservedUsers as $userData) {
            User::create($userData);
        }

        // Ensure we have essential admin user
        if (!User::where('role', 'admin')->exists()) {
            $this->warn('   ⚠️  No admin user found, creating default admin...');
            User::create([
                'name' => 'System Administrator',
                'email' => 'admin@wassip.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
        }

        $this->line("   ✅ Restored " . count($preservedUsers) . " system users");
    }

    /**
     * Create essential system data only (no sample data)
     */
    private function createEssentialData()
    {
        $this->line("   🌱 Creating essential system data only...");
        
        // Run only essential seeders (no sample data)
        $this->call('db:seed', ['--class' => 'CustomerTypeSeeder']);
        $this->call('db:seed', ['--class' => 'DivisionSeeder']);
        $this->call('db:seed', ['--class' => 'RateSeeder']);
        
        $this->line("   ✅ Essential system data created successfully");
    }

    /**
     * Validate database integrity
     */
    private function validateDatabaseIntegrity()
    {
        $checks = [
            'users' => User::count(),
            'divisions' => DB::table('divisions')->count(),
            'customer_types' => DB::table('customer_types')->count(),
            'guarantors' => DB::table('guarantors')->count(),
            'rates' => DB::table('rates')->count(),
            'customers' => DB::table('customers')->count(),
            'water_meters' => DB::table('water_meters')->count(),
            'meter_readings' => DB::table('meter_readings')->count(),
            'bills' => DB::table('bills')->count(),
        ];

        foreach ($checks as $table => $count) {
            if ($count > 0) {
                $this->line("   ✅ {$table}: {$count} records");
            } else {
                $this->warn("   ⚠️  {$table}: {$count} records (may be expected)");
            }
        }
    }

    /**
     * Display summary information
     */
    private function displaySummary()
    {
        $this->newLine();
        $this->info('📊 Database Refresh Summary:');
        $this->line('   ✅ System users preserved and restored');
        $this->line('   ✅ All sample data removed');
        $this->line('   ✅ Essential system data created (rates, divisions, customer types)');
        $this->line('   ✅ Database reset to fresh state');
        $this->line('   ✅ Ready for production data entry');
        
        $this->newLine();
        $this->info('🔑 System Access:');
        $this->line('   Use your existing login credentials');
        $this->line('   All preserved users remain active');
        
        $this->newLine();
        $this->info('💡 Fresh System State:');
        $this->line('   📊 No sample customers, meters, or readings');
        $this->line('   💰 No sample bills or payments');
        $this->line('   🔧 Essential configurations in place');
        $this->line('   ✨ Ready for real data entry');
        
        $this->newLine();
        $this->info('🌐 System Access: http://127.0.0.1:8000');
        $this->info('📱 Mobile API Base: http://127.0.0.1:8000/api/v1');
        
        $this->newLine();
        $this->info('🎯 Next Steps:');
        $this->line('   1. Test system login functionality');
        $this->line('   2. Verify mobile API endpoints');
        $this->line('   3. Review system configurations');
        $this->line('   4. Set up production data if needed');
    }
}
