<?php

namespace App\Console\Commands;

use App\Models\SmsNotification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class RemoveDuplicateSmsLogs extends Command
{
    protected $signature = 'sms:remove-duplicate-logs {--apply : Delete the duplicates (without this the command only counts them)}';

    protected $description = 'Remove the extra customer-less log rows that older versions wrote for every SMS sent';

    public function handle(): int
    {
        $query = $this->duplicates();

        $byStatus = (clone $query)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        if ($byStatus->isEmpty()) {
            $this->info('No duplicate SMS log rows found.');

            return self::SUCCESS;
        }

        $this->table(['Status', 'Duplicate rows'], $byStatus->map(fn ($total, $status) => [$status, $total])->values()->all());
        $this->line('A duplicate is a row with no customer, bill or payment whose phone number and message match a real row that has a customer.');

        if (!$this->option('apply')) {
            $this->warn('Dry run only. Run again with --apply to delete these rows.');

            return self::SUCCESS;
        }

        $deleted = 0;
        $this->duplicates()->select('id')->chunkById(500, function ($rows) use (&$deleted) {
            $deleted += SmsNotification::whereIn('id', $rows->pluck('id'))->delete();
        });

        $this->info("Deleted {$deleted} duplicate SMS log rows.");

        return self::SUCCESS;
    }

    private function duplicates(): Builder
    {
        return SmsNotification::query()->legacyDuplicates();
    }
}
