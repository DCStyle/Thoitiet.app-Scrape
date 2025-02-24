<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop table if exists
        Schema::dropIfExists('sitemaps');

        Schema::create('sitemaps', function (Blueprint $table) {
            $table->id();
            $table->mediumText('url');
            $table->string('parent_path', 255)->nullable()->index();
            $table->timestamp('last_modified')->nullable();
            $table->integer('level')->default(0)->index();
            $table->boolean('is_index')->default(false);
            $table->string('priority', 10)->nullable();
            $table->string('changefreq', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sitemaps');
    }
};
