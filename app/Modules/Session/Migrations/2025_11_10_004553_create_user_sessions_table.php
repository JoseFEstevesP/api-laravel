<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('user_sessions', function (Blueprint $table) {
      $table->id();
      $table->string('user_id', 255);
      $table->string('session_id', 255);
      $table->string('refresh_token', 512)->nullable();
      $table->string('ip_address', 45)->nullable();
      $table->string('user_agent', 1023)->nullable();
      $table->timestamp('login_at');
      $table->timestamp('last_activity')->nullable();
      $table->timestamp('expires_at')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();

      $table->index('user_id', 'idx_user_sessions_user_id');
      $table->index('session_id', 'idx_user_sessions_session_id');
      $table->index('is_active', 'idx_user_sessions_active');
      $table->index(['user_id', 'is_active'], 'idx_user_sessions_user_active');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('user_sessions');
  }
};
