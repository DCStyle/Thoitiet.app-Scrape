<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('footer_columns', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Title of the column
            $table->timestamps();
        });

        Schema::create('footer_column_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('footer_column_id')->constrained('footer_columns')->onDelete('cascade');
            $table->string('label'); // Link label
            $table->string('url'); // Link URL
            $table->timestamps();
        });

        Schema::create('footer_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Key (e.g., copyright, social_links)
            $table->text('value'); // JSON or plain text value
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('footer_settings');
        Schema::dropIfExists('footer_column_items');
        Schema::dropIfExists('footer_columns');
    }
};
