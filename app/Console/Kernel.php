<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
  /**
   * Define the application's command schedule.
   */
  protected function schedule(Schedule $schedule): void
  {
    // $schedule->command('inspire')->hourly();

    // Programar la limpieza de sesiones a las 12:00 AM todos los días
    $schedule->command('sessions:clean')->dailyAt('00:00');

    // Programar la actualización de usuarios inactivos (3 meses sin acceso) a las 12:30 AM todos los días
    $schedule->command('users:process-inactive')->dailyAt('00:30');
  }

  /**
   * Register the commands for the application.
   */
  protected function commands(): void
  {
    $this->load(__DIR__ . '/Commands');

    require base_path('routes/console.php');
  }
}
