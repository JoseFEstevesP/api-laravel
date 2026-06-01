<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Session\Models\UserSession;

class CleanUserSessionsCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'sessions:clean
                            {--hours=24 : Number of hours after expiry to keep inactive sessions}
                            {--days=30 : Number of days after login to delete old sessions}
                            {--force : Force deletion of all inactive sessions regardless of time}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Clean expired or inactive user sessions';

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    $hours = (int) $this->option('hours');
    $days = (int) $this->option('days');
    $force = $this->option('force');

    $this->info('Starting session cleaning process...');

    if ($force) {
      $deletedCount = UserSession::where('is_active', false)->delete();
      $this->info("Force deleted {$deletedCount} inactive sessions.");
    } else {
      // Limpiar sesiones expiradas usando el método del modelo
      $cleanedCount = UserSession::cleanExpiredSessions($hours);
      $this->info("Cleaned {$cleanedCount} expired sessions.");

      // Eliminar sesiones muy antiguas
      $oldDeletedCount = UserSession::deleteOldSessions($days);
      $this->info(
        "Deleted {$oldDeletedCount} old sessions older than {$days} days.",
      );
    }

    $this->info('Session cleaning process completed.');

    return 0;
  }
}
