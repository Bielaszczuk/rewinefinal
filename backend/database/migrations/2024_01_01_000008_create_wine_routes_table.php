<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_routes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('country')->nullable()->index();
            $table->string('region')->nullable()->index();
            $table->string('subregion')->nullable();
            $table->integer('estimated_duration')->nullable();
            $table->integer('estimated_days')->nullable();
            $table->double('total_distance')->nullable();
            $table->string('difficulty')->nullable();
            $table->string('image_url')->nullable();
            $table->string('status', 50)->default('draft');
            $table->jsonb('recommended_wine_types')->nullable();
            $table->uuid('created_by');
            $table->timestampsTz();

            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->index('status');
        });

        Schema::create('wine_route_stops', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wine_route_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 11, 7)->nullable();
            $table->integer('stop_order');
            $table->integer('estimated_duration')->nullable();

            $table->foreign('wine_route_id')->references('id')->on('wine_routes')->cascadeOnDelete();
            $table->index(['wine_route_id', 'stop_order']);
        });

        Schema::create('route_wineries', function (Blueprint $table) {
            $table->uuid('route_id');
            $table->uuid('winery_id');
            $table->primary(['route_id', 'winery_id']);

            $table->foreign('route_id')->references('id')->on('wine_routes')->cascadeOnDelete();
            $table->foreign('winery_id')->references('id')->on('wineries')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_wineries');
        Schema::dropIfExists('wine_route_stops');
        Schema::dropIfExists('wine_routes');
    }
};
