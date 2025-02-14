<?php

declare(strict_types=1);

use App\Console\Commands\DeleteInactiveVenues;
use App\Console\Commands\UpdateVenueActivityStatus;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule
            ->command(UpdateVenueActivityStatus::class)
            ->dailyAt('04:00:00')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule
            ->command(DeleteInactiveVenues::class)
            ->dailyAt('05:00:00')
            ->withoutOverlapping()
            ->onOneServer();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require \App\Console\base_path('routes/console.php');
    }
}
