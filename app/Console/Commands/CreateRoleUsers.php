<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class CreateRoleUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:create-role-users {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all data and create users for each role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will delete ALL data in the database. Are you sure?')) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        $this->info('Clearing all data...');
        
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Get all table names
        $tables = DB::select('SHOW TABLES');
        $databaseName = DB::getDatabaseName();
        
        foreach ($tables as $table) {
            $tableName = $table->{'Tables_in_' . $databaseName};
            
            // Skip migrations table
            if ($tableName === 'migrations') {
                continue;
            }
            
            DB::table($tableName)->truncate();
            $this->line("Cleared table: {$tableName}");
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->info('Creating role users...');
        
        // Define users for each role
        $users = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@aquabill.olexto.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ],
            [
                'name' => 'System Manager',
                'email' => 'manager@aquabill.olexto.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'is_active' => true,
            ],
            [
                'name' => 'Staff Member',
                'email' => 'staff@aquabill.olexto.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'is_active' => true,
            ],
            [
                'name' => 'Meter Reader',
                'email' => 'reader@aquabill.olexto.com',
                'password' => Hash::make('password'),
                'role' => 'meter_reader',
                'is_active' => true,
            ],
        ];
        
        foreach ($users as $userData) {
            $user = User::create($userData);
            $this->line("Created user: {$user->name} ({$user->email}) - Role: {$user->role}");
        }
        
        $this->info('');
        $this->info('✅ Successfully cleared all data and created role users!');
        $this->info('');
        $this->info('Login credentials:');
        $this->info('Admin: admin@aquabill.olexto.com / password');
        $this->info('Manager: manager@aquabill.olexto.com / password');
        $this->info('Staff: staff@aquabill.olexto.com / password');
        $this->info('Meter Reader: reader@aquabill.olexto.com / password');
    }
} 