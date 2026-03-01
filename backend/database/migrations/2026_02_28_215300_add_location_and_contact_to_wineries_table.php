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
        Schema::table('wineries', function (Blueprint $table) {
            $table->string('address', 500)->nullable()->after('description');
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('contact_email', 255)->nullable()->after('longitude');
            $table->string('contact_phone', 50)->nullable()->after('contact_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wineries', function (Blueprint $table) {
            $table->dropColumn(['address', 'latitude', 'longitude', 'contact_email', 'contact_phone']);
        });
    }
};
