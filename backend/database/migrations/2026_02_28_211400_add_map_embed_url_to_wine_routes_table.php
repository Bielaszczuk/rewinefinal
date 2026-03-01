<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wine_routes', function (Blueprint $table) {
            $table->string('map_embed_url', 500)->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wine_routes', function (Blueprint $table) {
            $table->dropColumn('map_embed_url');
        });
    }
};
