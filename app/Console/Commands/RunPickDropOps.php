<?php

namespace App\Console\Commands;

use App\Services\ShiftOpsService;
use Illuminate\Console\Command;

class RunPickDropOps extends Command
{
    protected $signature = 'pickdrop:run-ops';

    protected $description = 'Auto-assign waiting requests, send payment/renewal reminders, delay alerts, and attendance jobs.';

    public function handle(ShiftOpsService $ops): int
    {
        $result = $ops->run();

        foreach ($result as $key => $count) {
            $this->info($key . ': ' . $count);
        }

        return self::SUCCESS;
    }
}
