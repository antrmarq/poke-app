<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use App\Libraries\Semester;
use Illuminate\Support\Facades\DB;

class Kerneel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('fetch:pokemon 100')->daily();
    }
}