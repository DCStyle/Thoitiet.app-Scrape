<?php

namespace Database\Seeders;

use App\Models\FooterColumn;
use App\Models\FooterColumnItem;
use App\Models\FooterSetting;
use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $this->truncateData();

        $mainMenus = [
            [
                'title' => 'Tin thời tiết',
                'icon' => '<i class="fa-solid fa-cloud-sun"></i>',
                'url' => '#',
                'order' => 2,
                'children' => [
                    ['title' => 'Thời tiết hàng ngày', 'url' => '/danh-muc/thoi-tiet-hang-ngay', 'order' => 1],
                    ['title' => 'Thời tiết du lịch', 'url' => '/danh-muc/thoi-tiet-du-lich', 'order' => 2],
                    ['title' => 'Thiên nhiên', 'url' => '/danh-muc/thien-nhien', 'order' => 3],
                    ['title' => 'Tin tổng hợp', 'url' => '/danh-muc/tin-tong-hop', 'order' => 4],
                    ['title' => 'Khám phá', 'url' => '/danh-muc/kham-pha', 'order' => 5],
                ]
            ]
        ];

        foreach ($mainMenus as $menu) {
            $children = $menu['children'] ?? null;
            unset($menu['children']);

            $parentMenu = Menu::create($menu);

            if ($children) {
                foreach ($children as $child) {
                    $child['parent_id'] = $parentMenu->id;
                    Menu::create($child);
                }
            }
        }
    }

    public function truncateData()
    {
        // Disable foreign key checks and clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Menu::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
