<?php

namespace Database\Seeders;

use App\Models\FooterColumn;
use App\Models\FooterColumnItem;
use App\Models\FooterSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FooterSeeder extends Seeder
{
    public function run(): void
    {
        $this->truncateData();

        // Create columns
        $columns = [
            [
                'title' => 'Example column 1',
                'items' => [
                    ['label' => 'Example link', 'url' => '#'],
                    ['label' => 'Example link', 'url' => '#'],
                    ['label' => 'Example link', 'url' => '#'],
                    ['label' => 'Example link', 'url' => '#'],
                    ['label' => 'Example link', 'url' => '#'],
                ]
            ],
            [
                'title' => 'Example column 2',
                'items' => [
                    ['label' => 'Example link', 'url' => '#'],
                    ['label' => 'Example link', 'url' => '#'],
                    ['label' => 'Example link', 'url' => '#'],
                    ['label' => 'Example link', 'url' => '#'],
                    ['label' => 'Example link', 'url' => '#'],
                ]
            ],
            [
                'title' => 'Example column 3',
                'items' => [
                    ['label' => 'Example link', 'url' => '#'],
                    ['label' => 'Example link', 'url' => '#'],
                    ['label' => 'Example link', 'url' => '#'],
                    ['label' => 'Example link', 'url' => '#'],
                    ['label' => 'Example link', 'url' => '#'],
                ]
            ],
            [
                'title' => 'Example column 4',
                'items' => [
                    ['label' => 'Example link', 'url' => '#'],
                    ['label' => 'Example link', 'url' => '#'],
                    ['label' => 'Example link', 'url' => '#'],
                    ['label' => 'Example link', 'url' => '#'],
                    ['label' => 'Example link', 'url' => '#'],
                ]
            ],
        ];

        foreach ($columns as $index => $columnData) {
            $column = FooterColumn::create([
                'title' => $columnData['title'],
                'order' => $index + 1
            ]);

            foreach ($columnData['items'] as $itemIndex => $item) {
                FooterColumnItem::create([
                    'footer_column_id' => $column->id,
                    'label' => $item['label'],
                    'url' => $item['url'],
                    'order' => $itemIndex + 1
                ]);
            }
        }

        // Create footer settings
        $settings = [
            'site_name' => 'thoitiet247.vn',
            'site_description' => 'Dự báo thời tiết 24/7',
            'email' => 'contact@thoitiet247.vn',
            'address' => 'Số 7A Lê Đức Thọ, Phường Mai Dịch, Quận Cầu Giấy, Hà Nội',
            'responsible_person' => 'Ông Nguyễn Văn A',
            'copyright' => 'Copyright © 2025 thoitiet247.vn, All Rights Reserved',

            'social_facebook' => 'https://www.facebook.com/#',
            'social_telegram' => 'https://t.me/#',
            'social_youtube' => 'https://www.youtube.com/#',
            'social_twitter' => 'https://twitter.com/#',
            'social_instagram' => 'https://www.instagram.com/#',
            'social_linkedin' => 'https://www.linkedin.com/#',
            'social_pinterest' => 'https://www.pinterest.com/#',
            'social_tiktok' => 'https://www.tiktok.com/#',

            'usage_policy' => '#',
            'privacy_policy' => '#',
            'contact' => '#',
        ];

        foreach ($settings as $key => $value) {
            FooterSetting::create([
                'key' => $key,
                'value' => $value
            ]);
        }
    }

    public function truncateData()
    {
        // Disable foreign key checks and clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        FooterColumnItem::truncate();
        FooterColumn::truncate();
        FooterSetting::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
