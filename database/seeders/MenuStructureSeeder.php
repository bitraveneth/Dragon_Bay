<?php

namespace Database\Seeders;

use App\Helpers\MenuHelper;
use App\Models\MenuGroup;
use App\Models\MenuItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $structure = MenuHelper::getFallbackMenu();

        foreach ($structure as $groupIndex => $group) {
            $menuGroup = MenuGroup::updateOrCreate(
                ['key' => Str::slug($group['title'])],
                [
                    'title'    => $group['title'],
                    'position' => $groupIndex,
                    'is_active'=> true,
                ]
            );

            foreach ($group['items'] as $itemIndex => $item) {
                $menuItem = MenuItem::updateOrCreate(
                    [
                        'menu_group_id' => $menuGroup->id,
                        'key'           => $item['name'],
                        'parent_id'     => null,
                    ],
                    [
                        'name'       => $item['name'],
                        'icon'       => $item['icon'] ?? null,
                        'path'       => $item['path'] ?? '#',
                        'permission' => $item['permission'] ?? null,
                        'position'   => $itemIndex,
                        'is_active'  => true,
                    ]
                );

                if (!empty($item['subItems'])) {
                    foreach ($item['subItems'] as $subIndex => $subItem) {
                        MenuItem::updateOrCreate(
                            [
                                'menu_group_id' => $menuGroup->id,
                                'parent_id'     => $menuItem->id,
                                'name'          => $subItem['name'],
                            ],
                            [
                                'icon'       => null,
                                'path'       => $subItem['path'] ?? '#',
                                'permission' => $subItem['permission'] ?? null,
                                'position'   => $subIndex,
                                'is_active'  => true,
                            ]
                        );
                    }
                }
            }
        }

        // Cleanup deprecated duplicate link:
        // "Supplier prices" previously pointed to the same path as "Suppliers".
        MenuItem::where('name', 'Supplier prices')
            ->where('path', '/admin/suppliers')
            ->delete();

        // Cleanup misplaced warehouse submenu.
        MenuItem::where('name', 'Expenses & allowances')
            ->where('path', '/admin/expenses')
            ->delete();
    }
}
