<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_ai_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wine_id');
            $table->string('language', 10);
            $table->jsonb('profile_json');
            $table->timestampsTz();

            $table->unique(['wine_id', 'language']);
            $table->foreign('wine_id')->references('id')->on('wines')->cascadeOnDelete();
        });

        Schema::create('wine_comparisons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wine_a_id');
            $table->uuid('wine_b_id');
            $table->string('language', 10);
            $table->jsonb('comparison_json');
            $table->timestampsTz();

            $table->unique(['wine_a_id', 'wine_b_id', 'language']);
            $table->foreign('wine_a_id')->references('id')->on('wines')->cascadeOnDelete();
            $table->foreign('wine_b_id')->references('id')->on('wines')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_comparisons');
        Schema::dropIfExists('wine_ai_profiles');
    }
};
