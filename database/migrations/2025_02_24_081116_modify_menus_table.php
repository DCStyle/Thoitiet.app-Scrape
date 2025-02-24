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
        // Add column 'icon' to 'menus' table
        Schema::table('menus', function (Blueprint $table) {
            $table->string('icon')->nullable()->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop column 'icon' from 'menus' table
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
