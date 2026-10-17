<?php

namespace App\Console\Commands;

use Database\Seeders\LargeSampleDataSeeder;
use Illuminate\Console\Command;

class AddThousandsSampleData extends Command
{
    protected $signature = 'sample:add
                            {count=1000 : Extra customers to add, with meters, readings, and bills}';

    protected $description = 'Add extra sample customers and their meters, readings, and bills without deleting existing rows';

    public function handle(): int
    {
        $count = (int) $this->argument('count');
        if ($count < 1) {
            $this->error('Count must be at least 1.');
            return self::FAILURE;
        }

        $seeder = new LargeSampleDataSeeder();
        $seeder->setContainer($this->laravel);
        $seeder->setCommand($this);
        $seeder->append($count);

        return self::SUCCESS;
    }
}
