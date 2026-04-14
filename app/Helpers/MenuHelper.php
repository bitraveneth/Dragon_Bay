<?php

namespace App\Helpers;

use App\Models\MenuGroup;
use Illuminate\Support\Facades\Route;

class MenuHelper
{
    /**
     * Main entry point used by the sidebar.
     * Reads from the database if menu groups exist,
     * otherwise falls back to the legacy static config.
     */
    public static function getMenuGroups(): array
    {
        $groups = MenuGroup::with([
            'items.children' => function ($query) {
                $query->where('is_active', true)->orderBy('position');
            },
            'items.children.children' => function ($query) {
                $query->where('is_active', true)->orderBy('position');
            },
        ])
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        if ($groups->isEmpty()) {
            return self::applyDomainVisibility(
                self::applyLogisticsTerminology(
                    self::normalizeSidebarGroups(
                        self::applyRuntimeMenuLinks(self::getFallbackMenu())
                    )
                )
            );
        }

        $menuGroups = $groups->map(function (MenuGroup $group) {
            return [
                'title' => $group->title,
                'items' => $group->items
                    ->where('is_active', true)
                    ->sortBy('position')
                    ->values()
                    ->map(function ($item) {
                        $data = [
                            'name'       => $item->name,
                            'icon'       => $item->icon,
                            'path'       => $item->path,
                            'permission' => $item->permission,
                        ];

                        // Build submenu items as a flat list: direct children
                        // plus any children of those children. This keeps the
                        // sidebar at a single dropdown level.
                        $directChildren = $item->children
                            ->where('is_active', true)
                            ->sortBy('position')
                            ->values();

                        $subItems = collect();

                        foreach ($directChildren as $child) {
                            $subItems->push([
                                'name' => $child->name,
                                'path' => $child->path,
                                'permission' => $child->permission,
                            ]);

                            $grandChildren = $child->children
                                ->where('is_active', true)
                                ->sortBy('position')
                                ->values();

                            foreach ($grandChildren as $grandchild) {
                                $subItems->push([
                                    'name' => $grandchild->name,
                                    'path' => $grandchild->path,
                                    'permission' => $grandchild->permission,
                                ]);
                            }
                        }

                        if ($subItems->isNotEmpty()) {
                            $data['subItems'] = $subItems
                                ->unique(fn (array $sub) => mb_strtolower(trim(($sub['name'] ?? '') . '|' . ($sub['path'] ?? ''))))
                                ->values()
                                ->all();
                        }

                        return $data;
                    })
                    ->unique(fn (array $item) => mb_strtolower(trim(($item['name'] ?? '') . '|' . ($item['path'] ?? ''))))
                    ->values()
                    ->all(),
            ];
        })->all();

        return self::applyDomainVisibility(
            self::applyLogisticsTerminology(
                self::normalizeSidebarGroups(
                    self::applyRuntimeMenuLinks($menuGroups)
                )
            )
        );
    }

    public static function isBusinessAreaVisible(string $key): bool
    {
        return ! in_array(
            mb_strtolower(trim($key)),
            self::hiddenBusinessAreas(),
            true
        );
    }

    public static function isAdminPathHidden(?string $path): bool
    {
        if ($path === null) {
            return false;
        }

        $path = '/' . ltrim(trim($path), '/');

        if (in_array($path, self::hiddenPaths(), true)) {
            return true;
        }

        foreach (self::hiddenPathPrefixes() as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return true;
            }
        }

        return false;
    }

    public static function isValidMenuPath(?string $path): bool
    {
        if ($path === null) {
            return true;
        }

        $path = trim($path);

        if ($path === '' || $path === '#') {
            return true;
        }

        if (! str_starts_with($path, '/admin')) {
            return true;
        }

        return in_array($path, self::knownAdminPaths(), true);
    }

    public static function matchesCurrentPath(?string $menuPath, ?string $currentPath): bool
    {
        if ($menuPath === null || $currentPath === null) {
            return false;
        }

        $menuPath = rtrim(trim($menuPath), '/');
        $currentPath = '/' . trim($currentPath, '/');
        $currentPath = rtrim($currentPath, '/');

        if ($menuPath === '') {
            return false;
        }

        if ($menuPath === '/admin') {
            return $currentPath === '/admin';
        }

        return $currentPath === $menuPath || str_starts_with($currentPath, $menuPath . '/');
    }

    public static function knownAdminPaths(): array
    {
        static $knownPaths = null;

        if ($knownPaths !== null) {
            return $knownPaths;
        }

        $knownPaths = collect(Route::getRoutes())
            ->filter(function ($route) {
                return in_array('GET', $route->methods(), true)
                    && ! str_contains($route->uri(), '{');
            })
            ->map(fn ($route) => '/' . ltrim($route->uri(), '/'))
            ->all();

        return $knownPaths;
    }

    /**
     * Static menu structure used for seeding and as a fallback
     * when no menu_groups records exist yet.
     */
    public static function getFallbackMenu(): array
    {
        return [
            [
                'title' => 'Overview',
                'items' => [
                    [
                        'name' => 'Dashboard',
                        'icon' => 'dashboard',
                        'path' => '/admin',
                    ],
                ],
            ],
            [
                'title' => 'Control (Masters & Settings)',
                'items' => [
                    [
                        'name' => 'Products',
                        'icon' => 'products',
                        'path' => '#',
                        'permission' => 'control.products',
                        'subItems' => [
                            ['name' => 'Products', 'path' => '/admin/products', 'permission' => 'control.products'],
                            ['name' => 'Materials (raw / service)', 'path' => '/admin/materials', 'permission' => 'control.products'],
                            ['name' => 'Packaging types', 'path' => '/admin/packaging', 'permission' => 'control.products'],
                            ['name' => 'Tax & VAT classes', 'path' => '/admin/tax-classes', 'permission' => 'control.products'],
                            // Product price list (uses dedicated route /admin/products-price-list)
                            ['name' => 'Price lists', 'path' => '/admin/products-price-list', 'permission' => 'control.products'],
                        ],
                    ],
                    [
                        'name' => 'Agents',
                        'icon' => 'agents',
                        'path' => '#',
                        'permission' => 'control.agents',
                        'subItems' => [
                            ['name' => 'Agents', 'path' => '/admin/agents', 'permission' => 'control.agents'],
                            ['name' => 'Commission rules', 'path' => '/admin/commission-rules', 'permission' => 'control.agents'],
                        ],
                    ],
                    [
                        'name' => 'Suppliers',
                        'icon' => 'suppliers',
                        'path' => '#',
                        'permission' => 'control.suppliers',
                        'subItems' => [
                            ['name' => 'Suppliers', 'path' => '/admin/suppliers', 'permission' => 'control.suppliers'],
                            ['name' => 'Purchase orders', 'path' => '/admin/purchase-orders', 'permission' => 'control.suppliers'],
                        ],
                    ],
                    [
                        'name' => 'Warehouses',
                        'icon' => 'warehouses',
                        'path' => '#',
                        'permission' => 'control.warehouses',
                        'subItems' => [
                            ['name' => 'Warehouses', 'path' => '/admin/warehouses', 'permission' => 'control.warehouses'],
                            ['name' => 'Warehouse locations', 'path' => '/admin/warehouse-locations', 'permission' => 'control.warehouses'],
                            ['name' => 'Vehicle registry', 'path' => '/admin/vehicles', 'permission' => 'control.warehouses'],
                            // Delivery zones & routes configuration
                            ['name' => 'Delivery zones & routes', 'path' => '/admin/delivery-routes', 'permission' => 'control.warehouses'],
                        ],
                    ],
                    [
                        'name' => 'Employees',
                        'icon' => 'employees',
                        'path' => '#',
                        'permission' => 'control.employees',
                        'subItems' => [
                            ['name' => 'Employees', 'path' => '/admin/employees', 'permission' => 'control.employees'],
                            ['name' => 'Contracts', 'path' => '/admin/contracts', 'permission' => 'control.employees'],
                            ['name' => 'Allowances', 'path' => '/admin/allowances', 'permission' => 'control.employees'],
                            ['name' => 'Equipment', 'path' => '/admin/equipment', 'permission' => 'control.employees'],
                            ['name' => 'Leaves', 'path' => '/admin/leaves', 'permission' => 'control.employees'],
                            ['name' => 'Location logs', 'path' => '/admin/locations', 'permission' => 'control.employees'],
                            ['name' => 'Badges', 'path' => '/admin/badges', 'permission' => 'control.employees'],
                        ],
                    ],
                    [
                        'name' => 'System settings',
                        'icon' => 'settings',
                        'path' => '#',
                        'permission' => 'system.settings',
                        'subItems' => [
                            ['name' => 'Application settings', 'path' => '/admin/settings', 'permission' => 'system.settings'],
                            ['name' => 'Client manual', 'path' => '/admin/client-guide', 'permission' => 'system.settings'],
                            ['name' => 'Audit logs', 'path' => '/admin/audit-logs', 'permission' => 'audit.view'],
                            ['name' => 'User manager', 'path' => '/admin/users', 'permission' => 'roles.manage'],
                            ['name' => 'Role manager', 'path' => '/admin/roles', 'permission' => 'roles.manage'],
                            ['name' => 'Permission manager', 'path' => '/admin/permissions', 'permission' => 'permissions.manage'],
                            ['name' => 'Menu manager', 'path' => '/admin/menu', 'permission' => 'permissions.manage'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Manufacturing',
                'items' => [
                    [
                        'name' => 'Manufacturing',
                        'icon' => 'manufacturing',
                        'path' => '#',
                        'permission' => 'manufacturing.manage',
                        'subItems' => [
                            ['name' => 'Manufacturing dashboard', 'path' => '/admin/manufacturing-dashboard', 'permission' => 'manufacturing.manage'],
                            ['name' => 'BOMs (Bill of Materials)', 'path' => '/admin/boms', 'permission' => 'manufacturing.manage'],
                            ['name' => 'Production orders & runs', 'path' => '/admin/production', 'permission' => 'manufacturing.manage'],
                            ['name' => 'Pending receipts', 'path' => '/admin/production/pending-receipts', 'permission' => 'manufacturing.manage'],
                            ['name' => 'Batches & lots', 'path' => '/admin/batches', 'permission' => 'manufacturing.manage'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Inventory (Core operations)',
                'items' => [
                    [
                        'name' => 'Inventory',
                        'icon' => 'inventory',
                        'path' => '#',
                        'permission' => 'inventory.manage',
                        'subItems' => [
                            ['name' => 'Inventory Dashboard', 'path' => '/admin/inventory', 'permission' => 'inventory.manage'],
                            ['name' => 'Material stock', 'path' => '/admin/inventory/materials', 'permission' => 'inventory.manage'],
                            ['name' => 'Goods receipts (GRN)', 'path' => '/admin/goods-receipts', 'permission' => 'inventory.manage'],
                            ['name' => 'Stock Movements', 'path' => '/admin/stock/movements', 'permission' => 'inventory.manage'],
                            ['name' => 'Transfers', 'path' => '/admin/stock/transfers', 'permission' => 'inventory.manage'],
                            ['name' => 'Deliveries & POD', 'path' => '/admin/deliveries/pod', 'permission' => 'control.warehouses'],
                            ['name' => 'Vehicle loads', 'path' => '/admin/vehicle-load', 'permission' => 'control.warehouses'],
                            ['name' => 'Packing slips', 'path' => '/admin/deliveries/packing-slips', 'permission' => 'control.warehouses'],
                            ['name' => 'Picking lists', 'path' => '/admin/orders-picking', 'permission' => 'sales.manage'],
                            ['name' => 'Inventory adjustments', 'path' => '/admin/stock/audit', 'permission' => 'inventory.manage'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Sales',
                'items' => [
                    [
                        'name' => 'Sales',
                        'icon' => 'sales',
                        'path' => '#',
                        'permission' => 'sales.manage',
                        'subItems' => [
                            ['name' => 'Sales dashboard', 'path' => '/admin/sales-dashboard', 'permission' => 'sales.manage'],
                            ['name' => 'Sales orders', 'path' => '/admin/orders', 'permission' => 'sales.manage'],
                            ['name' => 'Shipments', 'path' => '/admin/shipments', 'permission' => 'logistics.shipments'],
                            ['name' => 'Sales targets', 'path' => '/admin/sales-targets', 'permission' => 'sales.manage'],
                            ['name' => 'Returns', 'path' => '/admin/returns/customer', 'permission' => 'sales.manage'],
                            ['name' => 'Customer gifts', 'path' => '/admin/gifts', 'permission' => 'sales.manage'],
                            ['name' => 'Marketing campaigns', 'path' => '/admin/campaigns', 'permission' => 'sales.manage'],
                            ['name' => 'Commission report', 'path' => '/admin/commissions', 'permission' => 'sales.manage'],
                            ['name' => 'Commission settlements', 'path' => '/admin/settlements', 'permission' => 'sales.manage'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Accounting',
                'items' => [
                    [
                        'name' => 'Accounting',
                        'icon' => 'accounting',
                        'path' => '#',
                        'permission' => 'accounting.manage',
                        'subItems' => [
                            ['name' => 'Accounting dashboard', 'path' => '/admin/accounting-dashboard', 'permission' => 'accounting.manage'],
                            ['name' => 'Customer invoices', 'path' => '/admin/finance', 'permission' => 'accounting.manage'],
                            ['name' => 'Agent advances', 'path' => '/admin/agent-advances', 'permission' => 'accounting.manage'],
                            ['name' => 'Purchase bills', 'path' => '/admin/bills', 'permission' => 'accounting.manage'],
                            ['name' => 'Expenses', 'path' => '/admin/expenses', 'permission' => 'accounting.manage'],
                            ['name' => 'Salary distributions', 'path' => '/admin/salary-distributions', 'permission' => 'accounting.manage'],
                            ['name' => 'Chart of accounts', 'path' => '/admin/accounts', 'permission' => 'accounting.manage'],
                            ['name' => 'Bank reconciliation', 'path' => '/admin/finance/reconciliation', 'permission' => 'accounting.manage'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Reports & analytics',
                'items' => [
                    [
                        'name' => 'Reports',
                        'icon' => 'reports',
                        'path' => '#',
                        'permission' => 'reports.view',
                        'subItems' => [
                            ['name' => 'Reports dashboard', 'path' => '/admin/reports-dashboard', 'permission' => 'reports.view'],
                            ['name' => 'Profit & Loss', 'path' => '/admin/reports/pl', 'permission' => 'reports.view'],
                            ['name' => 'Balance sheet', 'path' => '/admin/reports/bs', 'permission' => 'reports.view'],
                            ['name' => 'Cashflow', 'path' => '/admin/reports/cashflow', 'permission' => 'reports.view'],
                            ['name' => 'Tax report', 'path' => '/admin/reports/vat', 'permission' => 'reports.view'],
                            ['name' => 'Agent performance', 'path' => '/admin/reports/agents', 'permission' => 'reports.view'],
                            ['name' => 'Shipment profitability', 'path' => '/admin/reports/shipments/profitability', 'permission' => 'reports.view'],
                            ['name' => 'KG vs CBM usage', 'path' => '/admin/reports/shipments/weight-usage', 'permission' => 'reports.view'],
                            ['name' => 'Air vs Sea performance', 'path' => '/admin/reports/shipments/mode-performance', 'permission' => 'reports.view'],
                            ['name' => 'Production reports', 'path' => '/admin/reports/production', 'permission' => 'reports.view'],
                            ['name' => 'Payroll summary', 'path' => '/admin/reports/payroll', 'permission' => 'reports.view'],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected static function ensureSystemSettingsLinks(array $groups): array
    {
        foreach ($groups as &$group) {
            foreach ($group['items'] as &$item) {
                if (mb_strtolower($item['name'] ?? '') !== 'system settings') {
                    continue;
                }

                $subItems = collect($item['subItems'] ?? []);
                $subItems->push([
                    'name' => 'Application settings',
                    'path' => '/admin/settings',
                    'permission' => 'system.settings',
                ]);

                $item['subItems'] = $subItems
                    ->unique(fn (array $sub) => mb_strtolower(trim(($sub['name'] ?? '') . '|' . ($sub['path'] ?? ''))))
                    ->values()
                    ->all();
            }
        }

        return $groups;
    }

    protected static function ensureWarehouseLocationLinks(array $groups): array
    {
        foreach ($groups as &$group) {
            foreach ($group['items'] as &$item) {
                if (mb_strtolower($item['name'] ?? '') !== 'warehouses') {
                    continue;
                }

                $subItems = collect($item['subItems'] ?? []);
                $subItems->push([
                    'name' => 'Warehouse locations',
                    'path' => '/admin/warehouse-locations',
                    'permission' => 'control.warehouses',
                ]);

                $item['subItems'] = $subItems
                    ->unique(fn (array $sub) => mb_strtolower(trim(($sub['name'] ?? '') . '|' . ($sub['path'] ?? ''))))
                    ->values()
                    ->all();
            }
        }

        return $groups;
    }

    protected static function ensureAgentAdvanceLinks(array $groups): array
    {
        foreach ($groups as &$group) {
            foreach ($group['items'] as &$item) {
                if (mb_strtolower($item['name'] ?? '') !== 'accounting') {
                    continue;
                }

                $subItems = collect($item['subItems'] ?? []);
                $subItems->push([
                    'name' => 'Agent advances',
                    'path' => '/admin/agent-advances',
                    'permission' => 'accounting.manage',
                ]);

                $item['subItems'] = $subItems
                    ->unique(fn (array $sub) => mb_strtolower(trim(($sub['name'] ?? '') . '|' . ($sub['path'] ?? ''))))
                    ->values()
                    ->all();
            }
        }

        return $groups;
    }

    protected static function ensureStockMovementLinks(array $groups): array
    {
        foreach ($groups as &$group) {
            foreach ($group['items'] as &$item) {
                if (mb_strtolower($item['name'] ?? '') !== 'inventory') {
                    continue;
                }

                $subItems = collect($item['subItems'] ?? []);
                $subItems->push([
                    'name' => 'Stock Movements',
                    'path' => '/admin/stock/movements',
                    'permission' => 'inventory.manage',
                ]);

                $item['subItems'] = $subItems
                    ->unique(fn (array $sub) => mb_strtolower(trim(($sub['name'] ?? '') . '|' . ($sub['path'] ?? ''))))
                    ->values()
                    ->all();
            }
        }

        return $groups;
    }

    protected static function applyRuntimeMenuLinks(array $groups): array
    {
        return self::ensureLogisticsReportLinks(
            self::ensureAuditLogLinks(
                self::ensureShipmentLinks(
                    self::ensureStockMovementLinks(
                        self::ensureAgentAdvanceLinks(
                            self::ensureWarehouseLocationLinks(
                                self::ensureSystemSettingsLinks($groups)
                            )
                        )
                    )
                )
            )
        );
    }

    protected static function ensureAuditLogLinks(array $groups): array
    {
        foreach ($groups as &$group) {
            foreach ($group['items'] as &$item) {
                $name = mb_strtolower(trim($item['name'] ?? ''));
                $path = '/' . ltrim(trim($item['path'] ?? ''), '/');

                if (! in_array($name, ['system settings', 'system & access'], true) && $path !== '/admin/settings') {
                    continue;
                }

                $subItems = collect($item['subItems'] ?? []);
                $subItems->push([
                    'name' => 'Audit logs',
                    'path' => '/admin/audit-logs',
                    'permission' => 'audit.view',
                ]);

                $item['subItems'] = $subItems
                    ->unique(fn (array $sub) => mb_strtolower(trim(($sub['name'] ?? '') . '|' . ($sub['path'] ?? ''))))
                    ->values()
                    ->all();
            }
        }

        return $groups;
    }

    protected static function ensureLogisticsReportLinks(array $groups): array
    {
        foreach ($groups as &$group) {
            $title = mb_strtolower(trim($group['title'] ?? ''));

            if (! in_array($title, ['reports & analytics', 'insights & reports'], true)) {
                continue;
            }

            foreach ($group['items'] as &$item) {
                $name = mb_strtolower(trim($item['name'] ?? ''));
                $path = trim((string) ($item['path'] ?? ''));

                if (! in_array($name, ['reports', 'insights'], true) && $path !== '#') {
                    continue;
                }

                $subItems = collect($item['subItems'] ?? []);
                $subItems->push(
                    ['name' => 'Shipment profitability', 'path' => '/admin/reports/shipments/profitability', 'permission' => 'reports.view'],
                    ['name' => 'KG vs CBM usage', 'path' => '/admin/reports/shipments/weight-usage', 'permission' => 'reports.view'],
                    ['name' => 'Air vs Sea performance', 'path' => '/admin/reports/shipments/mode-performance', 'permission' => 'reports.view'],
                );

                $item['subItems'] = $subItems
                    ->unique(fn (array $sub) => mb_strtolower(trim(($sub['name'] ?? '') . '|' . ($sub['path'] ?? ''))))
                    ->values()
                    ->all();
            }
        }

        return $groups;
    }

    protected static function ensureShipmentLinks(array $groups): array
    {
        foreach ($groups as &$group) {
            $title = mb_strtolower(trim($group['title'] ?? ''));
            if (! in_array($title, ['sales', 'orders & clients'], true)) {
                continue;
            }

            foreach ($group['items'] as &$item) {
                $name = mb_strtolower(trim($item['name'] ?? ''));
                if (! in_array($name, ['sales', 'orders & clients', 'orders & clients'], true)) {
                    continue;
                }

                $subItems = collect($item['subItems'] ?? []);
                $subItems->push([
                    'name' => 'Shipments',
                    'path' => '/admin/shipments',
                    'permission' => 'logistics.shipments',
                ]);

                $item['subItems'] = $subItems
                    ->unique(fn (array $sub) => mb_strtolower(trim(($sub['name'] ?? '') . '|' . ($sub['path'] ?? ''))))
                    ->values()
                    ->all();
            }
        }

        return $groups;
    }

    protected static function normalizeSidebarGroups(array $groups): array
    {
        foreach ($groups as &$group) {
            $groupTitle = mb_strtolower(trim($group['title'] ?? ''));

            foreach ($group['items'] as &$item) {
                $subItems = collect($item['subItems'] ?? []);

                if ($groupTitle !== 'reports & analytics') {
                    $subItems = $subItems->reject(function (array $subItem) {
                        $name = mb_strtolower(trim($subItem['name'] ?? ''));
                        $path = trim($subItem['path'] ?? '');

                        return ($name === 'production analysis' && $path === '/admin/reports/production')
                            || ($name === 'help & configuration guide' && $path === '/admin/help')
                            || $path === '/admin/help';
                    });
                }

                $item['subItems'] = $subItems->values()->all();
            }
        }

        return $groups;
    }

    protected static function applyDomainVisibility(array $groups): array
    {
        return collect($groups)
            ->reject(function (array $group) {
                return in_array(
                    mb_strtolower(trim($group['title'] ?? '')),
                    self::sidebarHiddenGroupTitles(),
                    true
                );
            })
            ->map(function (array $group) {
                $group['items'] = collect($group['items'] ?? [])
                    ->map(function (array $item) {
                        $item['subItems'] = collect($item['subItems'] ?? [])
                            ->reject(function (array $subItem) {
                                return self::isSidebarPathHidden($subItem['path'] ?? null);
                            })
                            ->values()
                            ->all();

                        return $item;
                    })
                    ->reject(function (array $item) {
                        $path = trim((string) ($item['path'] ?? ''));
                        $subItems = $item['subItems'] ?? [];

                        return self::isSidebarPathHidden($path)
                            || ($path === '#' && empty($subItems));
                    })
                    ->values()
                    ->all();

                return $group;
            })
            ->filter(fn (array $group) => ! empty($group['items']))
            ->values()
            ->all();
    }

    protected static function applyLogisticsTerminology(array $groups): array
    {
        return collect($groups)
            ->map(function (array $group) {
                $group['title'] = self::logisticsGroupTitle($group['title'] ?? '');

                $group['items'] = collect($group['items'] ?? [])
                    ->map(function (array $item) {
                        $item['name'] = self::logisticsMenuLabel(
                            $item['name'] ?? '',
                            $item['path'] ?? null
                        );

                        $item['subItems'] = collect($item['subItems'] ?? [])
                            ->map(function (array $subItem) {
                                $subItem['name'] = self::logisticsMenuLabel(
                                    $subItem['name'] ?? '',
                                    $subItem['path'] ?? null
                                );

                                return $subItem;
                            })
                            ->all();

                        return $item;
                    })
                    ->all();

                return $group;
            })
            ->all();
    }

    protected static function logisticsGroupTitle(string $title): string
    {
        $map = [
            'control (masters & settings)' => 'Masters & Setup',
            'inventory (core operations)' => 'Operations & Fulfillment',
            'sales' => 'Orders & Clients',
            'accounting' => 'Finance & Billing',
            'reports & analytics' => 'Insights & Reports',
        ];

        $normalized = mb_strtolower(trim($title));

        return $map[$normalized] ?? $title;
    }

    protected static function logisticsMenuLabel(string $name, ?string $path): string
    {
        $pathMap = [
            '/admin/products' => 'Catalog',
            '/admin/products-price-list' => 'Rate cards',
            '/admin/agents' => 'Clients',
            '/admin/commission-rules' => 'Client pricing rules',
            '/admin/suppliers' => 'Vendors',
            '/admin/purchase-orders' => 'Vendor purchase orders',
            '/admin/warehouses' => 'Hubs & warehouses',
            '/admin/warehouse-locations' => 'Hub locations',
            '/admin/vehicles' => 'Vehicle registry',
            '/admin/delivery-routes' => 'Delivery routes',
            '/admin/settings' => 'Application settings',
            '/admin/client-guide' => 'Operations manual',
            '/admin/audit-logs' => 'Audit logs',
            '/admin/users' => 'User manager',
            '/admin/roles' => 'Role manager',
            '/admin/permissions' => 'Permission manager',
            '/admin/menu' => 'Navigation manager',
            '/admin/inventory' => 'Operations dashboard',
            '/admin/goods-receipts' => 'Inbound receipts',
            '/admin/stock/movements' => 'Stock movements',
            '/admin/stock/transfers' => 'Hub transfers',
            '/admin/deliveries/pod' => 'Dispatch & POD',
            '/admin/vehicle-load' => 'Load planning',
            '/admin/deliveries/packing-slips' => 'Dispatch slips',
            '/admin/orders-picking' => 'Pick lists',
            '/admin/stock/audit' => 'Stock adjustments',
            '/admin/sales-dashboard' => 'Orders dashboard',
            '/admin/orders' => 'Client orders',
            '/admin/shipments' => 'Shipments',
            '/admin/sales-targets' => 'Account targets',
            '/admin/returns/customer' => 'Client returns',
            '/admin/commissions' => 'Commission statements',
            '/admin/settlements' => 'Commission payouts',
            '/admin/accounting-dashboard' => 'Finance dashboard',
            '/admin/finance' => 'Client invoices',
            '/admin/agent-advances' => 'Commission advances',
            '/admin/bills' => 'Vendor bills',
            '/admin/expenses' => 'Operational expenses',
            '/admin/accounts' => 'Ledger accounts',
            '/admin/finance/reconciliation' => 'Bank reconciliation',
            '/admin/reports-dashboard' => 'Insights dashboard',
            '/admin/reports/pl' => 'Profit & loss',
            '/admin/reports/bs' => 'Balance sheet',
            '/admin/reports/cashflow' => 'Cashflow',
            '/admin/reports/vat' => 'Tax report',
            '/admin/reports/agents' => 'Client performance',
            '/admin/reports/shipments/profitability' => 'Shipment profitability',
            '/admin/reports/shipments/weight-usage' => 'KG vs CBM usage',
            '/admin/reports/shipments/mode-performance' => 'Air vs Sea performance',
        ];

        if ($path !== null) {
            $normalizedPath = '/' . ltrim(trim($path), '/');

            if (isset($pathMap[$normalizedPath])) {
                return $pathMap[$normalizedPath];
            }
        }

        $nameMap = [
            'products' => 'Catalog',
            'agents' => 'Clients',
            'suppliers' => 'Vendors',
            'warehouses' => 'Hubs & routes',
            'system settings' => 'System & access',
            'inventory' => 'Operations',
            'sales' => 'Orders & clients',
            'accounting' => 'Finance & billing',
            'reports' => 'Insights',
        ];

        $normalizedName = mb_strtolower(trim($name));

        return $nameMap[$normalizedName] ?? $name;
    }

    protected static function hiddenBusinessAreas(): array
    {
        if (! self::legacyModuleHidingEnabled()) {
            return [];
        }

        return [
            'manufacturing',
            'employees',
            'crm',
        ];
    }

    protected static function hiddenGroupTitles(): array
    {
        if (! self::legacyModuleHidingEnabled()) {
            return [];
        }

        return [
            'manufacturing',
        ];
    }

    protected static function sidebarHiddenGroupTitles(): array
    {
        return [
            'manufacturing',
        ];
    }

    protected static function isSidebarPathHidden(?string $path): bool
    {
        if ($path === null) {
            return false;
        }

        $path = '/' . ltrim(trim($path), '/');

        if (in_array($path, self::sidebarHiddenPaths(), true)) {
            return true;
        }

        foreach (self::sidebarHiddenPathPrefixes() as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return true;
            }
        }

        return false;
    }

    protected static function sidebarHiddenPaths(): array
    {
        return [
            // Old manufacturing/product-stock ERP areas not needed for the logistics SRS.
            '/admin/products',
            '/admin/materials',
            '/admin/packaging',
            '/admin/tax-classes',
            '/admin/products-price-list',
            '/admin/boms',
            '/admin/production',
            '/admin/production/pending-receipts',
            '/admin/batches',
            '/admin/manufacturing-dashboard',

            // Supplier procurement and stock-control pages from the previous ERP.
            '/admin/suppliers',
            '/admin/purchase-orders',
            '/admin/inventory',
            '/admin/inventory/materials',
            '/admin/goods-receipts',
            '/admin/stock/movements',
            '/admin/stock/transfers',
            '/admin/deliveries/packing-slips',
            '/admin/orders-picking',
            '/admin/stock/audit',

            // HR and CRM extras outside courier/air/sea/DDP logistics.
            '/admin/employees',
            '/admin/contracts',
            '/admin/allowances',
            '/admin/equipment',
            '/admin/leaves',
            '/admin/locations',
            '/admin/badges',
            '/admin/sales-targets',
            '/admin/returns/customer',
            '/admin/campaigns',
            '/admin/gifts',
            '/admin/salary-distributions',

            // Reports tied to hidden business areas.
            '/admin/reports/production',
            '/admin/reports/payroll',

            // Vendor bills are replaced by shipment expense capture.
            '/admin/bills',
        ];
    }

    protected static function sidebarHiddenPathPrefixes(): array
    {
        return [
            '/admin/products',
            '/admin/materials',
            '/admin/packaging',
            '/admin/tax-classes',
            '/admin/suppliers',
            '/admin/purchase-orders',
            '/admin/boms',
            '/admin/production',
            '/admin/batches',
            '/admin/employees',
            '/admin/contracts',
            '/admin/allowances',
            '/admin/equipment',
            '/admin/leaves',
            '/admin/locations',
            '/admin/badges',
            '/admin/sales-targets',
            '/admin/returns/customer',
            '/admin/campaigns',
            '/admin/gifts',
            '/admin/salary-distributions',
            '/admin/bills',
        ];
    }

    protected static function hiddenPaths(): array
    {
        if (! self::legacyModuleHidingEnabled()) {
            return [];
        }

        return [
            '/admin/manufacturing-dashboard',
            '/admin/materials',
            '/admin/boms',
            '/admin/production',
            '/admin/production/pending-receipts',
            '/admin/batches',
            '/admin/inventory/materials',
            '/admin/employees',
            '/admin/contracts',
            '/admin/allowances',
            '/admin/equipment',
            '/admin/leaves',
            '/admin/locations',
            '/admin/badges',
            '/admin/campaigns',
            '/admin/gifts',
            '/admin/reports/production',
            '/admin/reports/payroll',
            '/admin/salary-distributions',
        ];
    }

    protected static function hiddenPathPrefixes(): array
    {
        if (! self::legacyModuleHidingEnabled()) {
            return [];
        }

        return [
            '/admin/employees',
            '/admin/contracts',
            '/admin/allowances',
            '/admin/equipment',
            '/admin/leaves',
            '/admin/locations',
            '/admin/badges',
            '/admin/materials',
            '/admin/boms',
            '/admin/production',
            '/admin/batches',
            '/admin/campaigns',
            '/admin/gifts',
            '/admin/gifts',
            '/admin/salary-distributions',
        ];
    }

    protected static function legacyModuleHidingEnabled(): bool
    {
        return (bool) config('app.hide_legacy_modules', false);
    }

    public static function getIconSvg(string $key): string
    {
        // Simple icon set, reused across the sidebar.
        $icons = [
            'dashboard' => <<<'SVG'
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V9C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 9V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V9C9.75 9.41421 9.41421 9.75 9 9.75H5.5C5.08579 9.75 4.75 9.41421 4.75 9V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7426 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V9C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 9V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V9C14.25 9.41421 14.5858 9.75 15 9.75H18.5C18.9142 9.75 19.25 9.41421 19.25 9V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7426 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z" fill="currentColor"/>
</svg>
SVG,
            'products' => <<<'SVG'
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M4 9L12 4L20 9V15L12 20L4 15V9Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
  <path d="M4 9L12 14L20 9" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
</svg>
SVG,
            'agents' => <<<'SVG'
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="12" cy="7.5" r="3.5" stroke="currentColor" stroke-width="1.5"/>
  <path d="M5 19C6.4 16.5 8.9 15 12 15C15.1 15 17.6 16.5 19 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
</svg>
SVG,
            'suppliers' => <<<'SVG'
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect x="3" y="7" width="11" height="10" rx="2" stroke="currentColor" stroke-width="1.5"/>
  <path d="M14 10H18L21 13V17H18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG,
            'warehouses' => <<<'SVG'
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M4 10L12 4L20 10V20H4V10Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
  <path d="M9 20V13H15V20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
</svg>
SVG,
            'employees' => <<<'SVG'
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="8" cy="9" r="3" stroke="currentColor" stroke-width="1.5"/>
  <circle cx="16" cy="9" r="3" stroke="currentColor" stroke-width="1.5"/>
  <path d="M3 19C3.8 16.8 5.6 15.5 8 15.5C10.4 15.5 12.2 16.8 13 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
  <path d="M11 19C11.8 16.8 13.6 15.5 16 15.5C18.4 15.5 20.2 16.8 21 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
</svg>
SVG,
            'settings' => <<<'SVG'
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/>
  <path d="M4 12H2M22 12H20M12 4V2M12 22V20M6.2 6.2L4.8 4.8M19.2 19.2L17.8 17.8M17.8 6.2L19.2 4.8M4.8 19.2L6.2 17.8"
        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
</svg>
SVG,
            'system' => <<<'SVG'
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect x="4" y="5" width="16" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/>
  <path d="M9 20H15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
  <path d="M12 17V20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
  <path d="M8 9H16M8 12H13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
</svg>
SVG,
            'manufacturing' => <<<'SVG'
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M4 15L10 9L14 13L20 7V19H4V15Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
</svg>
SVG,
            'inventory' => <<<'SVG'
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect x="4" y="4" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
  <rect x="13" y="4" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
  <rect x="4" y="13" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
  <rect x="13" y="13" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
</svg>
SVG,
            'sales' => <<<'SVG'
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M5 7H19L17.5 18H6.5L5 7Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
  <path d="M9 7L10 4H14L15 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
</svg>
SVG,
            'accounting' => <<<'SVG'
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M8 7H15C16.657 7 18 8.343 18 10C18 11.657 16.657 13 15 13H9C7.343 13 6 14.343 6 16C6 17.657 7.343 19 9 19H16"
        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
  <path d="M12 5V7M12 19V21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
</svg>
SVG,
            'reports' => <<<'SVG'
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect x="4" y="4" width="14" height="16" rx="2" stroke="currentColor" stroke-width="1.5"/>
  <path d="M9 9H15M9 12H15M9 15H13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
  <path d="M8 4V2H18V16H16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG,
            'default' => <<<'SVG'
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect x="4" y="4" width="16" height="16" rx="3" stroke="currentColor" stroke-width="1.5"/>
</svg>
SVG,
        ];

        return $icons[$key] ?? $icons['default'];
    }
}
