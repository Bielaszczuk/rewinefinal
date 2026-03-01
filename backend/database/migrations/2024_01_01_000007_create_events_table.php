<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 50);
            $table->timestampTz('start_date');
            $table->timestampTz('end_date');
            $table->string('location_name')->nullable();
            $table->string('location_address')->nullable();
            $table->string('location_city')->nullable();
            $table->string('location_region')->nullable();
            $table->decimal('latitude', 10, 7)->nullable()->index();
            $table->decimal('longitude', 11, 7)->nullable()->index();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('max_attendees')->nullable();
            $table->integer('current_attendees')->default(0);
            $table->string('status', 50)->default('DRAFT');
            $table->string('image_url')->nullable();
            $table->uuid('organizer_id');
            $table->string('organizer_type', 50)->default('PARTNER');
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('website_url')->nullable();
            $table->timestampsTz();

            $table->foreign('organizer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['status', 'start_date']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
