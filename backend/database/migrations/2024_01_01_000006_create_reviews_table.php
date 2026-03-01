<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wine_id');
            $table->uuid('user_id');
            $table->decimal('rating', 2, 1);
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->integer('helpful_count')->default(0);
            $table->timestampsTz();

            $table->unique(['user_id', 'wine_id']);
            $table->foreign('wine_id')->references('id')->on('wines')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('review_likes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('review_id');
            $table->uuid('user_id');
            $table->timestampTz('created_at')->nullable();

            $table->unique(['review_id', 'user_id']);
            $table->foreign('review_id')->references('id')->on('reviews')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('review_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('review_id');
            $table->uuid('user_id');
            $table->text('content');
            $table->timestampsTz();

            $table->foreign('review_id')->references('id')->on('reviews')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_comments');
        Schema::dropIfExists('review_likes');
        Schema::dropIfExists('reviews');
    }
};
