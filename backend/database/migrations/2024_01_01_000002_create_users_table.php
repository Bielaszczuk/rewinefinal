<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(\Illuminate\Support\Str::uuid());
            $table->string('username', 50)->unique();
            $table->string('email', 255)->unique();
            $table->string('password_hash', 255);
            $table->string('name', 100)->nullable();
            $table->string('avatar_url', 500)->nullable();
            $table->boolean('enabled')->default(true);
            $table->boolean('email_verified')->default(false);
            $table->boolean('locked')->default(false);
            $table->string('lock_reason', 255)->nullable();
            $table->timestampTz('last_login_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->bigInteger('role_id')->unsigned();
            $table->primary(['user_id', 'role_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('users');
    }
};
