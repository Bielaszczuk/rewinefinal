<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('winery_id')->nullable();
            $table->string('name')->index();
            $table->integer('vintage')->nullable();
            $table->string('wine_type', 50);
            $table->string('style')->nullable();
            $table->jsonb('grapes')->nullable();
            $table->jsonb('allergens')->nullable();
            $table->text('description_es')->nullable();
            $table->text('description_en')->nullable();
            $table->decimal('alcohol_content', 4, 2)->nullable();
            $table->integer('serving_temp_min')->nullable();
            $table->integer('serving_temp_max')->nullable();
            $table->decimal('price_min', 10, 2)->nullable();
            $table->decimal('price_max', 10, 2)->nullable();
            $table->string('image_url')->nullable();
            $table->decimal('rating_average', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->timestampsTz();

            $table->foreign('winery_id')->references('id')->on('wineries')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wines');
    }
};
