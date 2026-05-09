<?php

namespace App\Console\Commands;

use App\Models\MonthlyBill;
use Illuminate\Console\Command;

class MarkOverdueBills extends Command
{
    protected $signature = 'bills:mark-overdue';

    protected $description = 'Mark pending bills past their due date as overdue';

    public function handle(): int
    {
        $count = MonthlyBill::where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        $this->info("{$count} bills marked as overdue.");

        return self::SUCCESS;
    }
}