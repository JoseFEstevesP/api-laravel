<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('users', function (Blueprint $table) {
      $table->uuid('uid')->primary();
      $table->string('names', 255);
      $table->string('surnames', 255);
      $table->string('phone', 255);
      $table->string('email', 255)->unique();
      $table->string('password', 255)->nullable();
      $table->string('provider', 255)->default('local');
      $table->boolean('status')->default(true);
      $table->string('code', 255)->nullable();
      $table->boolean('activatedAccount')->default(false);
      $table->integer('attemptCount')->default(0);
      $table->timestamp('dataOfAttempt')->nullable();
      $table->uuid('uidRol');
      $table->timestamps();

      $table->index('uidRol', 'idx_user_uid_rol');
      $table->index('status', 'idx_user_status');
      $table->index('activatedAccount', 'idx_user_activated_account');
      $table->index('phone', 'idx_user_phone');
      $table->index(['status', 'uidRol'], 'idx_user_status_rol');
      $table->index(['status', 'activatedAccount'], 'idx_user_status_active');

      $table->foreign('uidRol')
        ->references('uid')
        ->on('roles');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('users');
  }
};
