<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('user_sessions', function (Blueprint $table) {
      $table->index('expires_at', 'idx_user_sessions_expires_at');
      $table->index('login_at', 'idx_user_sessions_login_at');
    });
  }

  public function down(): void
  {
    Schema::table('user_sessions', function (Blueprint $table) {
      $table->dropIndex('idx_user_sessions_expires_at');
      $table->dropIndex('idx_user_sessions_login_at');
    });
  }
};
