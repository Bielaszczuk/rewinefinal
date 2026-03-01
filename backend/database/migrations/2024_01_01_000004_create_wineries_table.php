<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wineries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->index();
            $table->string('country');
            $table->string('region')->nullable();
            $table->string('subregion')->nullable();
            $table->text('description')->nullable();
            $table->string('website_url')->nullable();
            $table->string('logo_url')->nullable();
            $table->integer('established')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wineries');
    }
};
