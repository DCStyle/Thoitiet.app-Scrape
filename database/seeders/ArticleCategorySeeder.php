<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticleCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Drop current data
        $this->truncateTable('article_categories');

        // Add new data
        $categories = [
            ['name' => 'Thời tiết hàng ngày', 'slug' => 'thoi-tiet-hang-ngay', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Thời tiết du lịch', 'slug' => 'thoi-tiet-du-lich', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Thiên nhiên', 'slug' => 'thien-nhien', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tin tổng hợp', 'slug' => 'tin-tong-hop', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Khám phá', 'slug' => 'kham-pha', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('article_categories')->insert($categories);
    }

    public function truncateTable($table): void
    {
        $this->command->info('Truncating ' . $table . ' table');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table($table)->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
