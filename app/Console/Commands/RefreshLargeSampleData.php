<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefreshLargeSampleData extends Command
{
    protected $signature = 'sample:refresh-large
                            {--force : Skip confirmation}';

    protected $description = 'Wipe every database table (except migrations) and load system users plus a large sample dataset';

    public function handle(): int
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This DELETES every row in the database, then recreates system users and a large sample dataset. Continue?')) {
                $this->warn('Cancelled.');
                return self::SUCCESS;
            }
        }

        $this->info('Clearing every table...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $database = DB::getDatabaseName();
        $key = 'Tables_in_' . $database;
        foreach (DB::select('SHOW TABLES') as $table) {
            $name = $table->{$key};
            if ($name === 'migrations') {
                continue;
            }
            $count = DB::table($name)->count();
            DB::table($name)->truncate();
            $this->line("  Truncated {$name} ({$count})");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->restoreSystemConfiguration();

        $this->newLine();
        $this->info('Seeding system users...');
        $this->call('db:seed', ['--class' => 'SystemUsersSeeder', '--force' => true]);

        $this->newLine();
        $this->info('Seeding large sample data...');
        $this->call('db:seed', ['--class' => 'LargeSampleDataSeeder', '--force' => true]);

        return self::SUCCESS;
    }

    private function restoreSystemConfiguration(): void
    {
        if (!Schema::hasTable('system_configurations')) {
            return;
        }

        $now = now();
        $rows = [
            ['key' => 'default_billing_day', 'value' => '1', 'type' => 'integer', 'description' => 'Default billing day of the month (1-31) for new customers'],
            ['key' => 'auto_billing_enabled_default', 'value' => '1', 'type' => 'boolean', 'description' => 'Default auto-billing status for new customers'],
            ['key' => 'billing_cycle_type', 'value' => 'monthly', 'type' => 'string', 'description' => 'Billing cycle frequency'],
            ['key' => 'system_enabled', 'value' => '1', 'type' => 'boolean', 'description' => 'Enable/disable system features for license control'],
            ['key' => 'system_disabled_at', 'value' => null, 'type' => 'datetime', 'description' => 'Timestamp when system was disabled'],
            ['key' => 'disable_reason', 'value' => null, 'type' => 'string', 'description' => 'Reason for disabling the system'],
        ];

        foreach ($rows as $row) {
            DB::table('system_configurations')->updateOrInsert(
                ['key' => $row['key']],
                $row + ['created_at' => $now, 'updated_at' => $now]
            );
        }

        $this->line('  Restored system configuration');
    }
}
