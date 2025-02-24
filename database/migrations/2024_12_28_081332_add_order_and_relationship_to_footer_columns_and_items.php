<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Adding order column to footer_columns table
        Schema::table('footer_columns', function (Blueprint $table) {
            $table->integer('order')->default(0)->after('title'); // Add `order` for sorting columns
        });

        // Adding order and updating relationship in footer_column_items table
        Schema::table('footer_column_items', function (Blueprint $table) {
            $table->integer('order')->default(0)->after('url'); // Add `order` for sorting items
            $table->foreignId('footer_column_id')->nullable()->change(); // Allow parent column to be updated
        });
    }

    public function down()
    {
        Schema::table('footer_columns', function (Blueprint $table) {
            $table->dropColumn('order');
        });

        Schema::table('footer_column_items', function (Blueprint $table) {
            $table->dropColumn('order');
            $table->foreignId('footer_column_id')->nullable(false)->change(); // Revert parent column to not nullable
        });
    }
};
