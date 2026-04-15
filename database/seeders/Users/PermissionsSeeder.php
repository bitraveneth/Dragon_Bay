<?php

namespace Database\Seeders\Users;

use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

/**
 * Seed initial permissions and role → permission mappings.
 *
 * Existing role-permission mappings are not reset on later runs, because
 * production admins can manage those assignments from the UI.
 */
class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            [
                'name'  => 'inventory.manage',
                'label' => 'Manage inventory & stock movements',
                'group' => 'Inventory & Stock',
                'roles' => ['warehouse_officer', 'delivery_coordinator'],
            ],
            [
                'name'  => 'control.products',
                'label' => 'Manage products, materials, packaging & tax classes',
                'group' => 'Masters & Control',
                'roles' => ['production_officer', 'purchase_executive'],
            ],
            [
                'name'  => 'control.agents',
                'label' => 'Manage agents, pricing & commissions',
                'group' => 'Masters & Control',
                'roles' => ['sales_officer', 'delivery_coordinator', 'accounts_officer'],
            ],
            [
                'name'  => 'control.suppliers',
                'label' => 'Manage suppliers & purchase masters',
                'group' => 'Masters & Control',
                'roles' => ['purchase_executive'],
            ],
            [
                'name'  => 'control.warehouses',
                'label' => 'Manage warehouses, locations & fleet',
                'group' => 'Masters & Control',
                'roles' => ['warehouse_officer', 'delivery_coordinator'],
            ],
            [
                'name'  => 'control.employees',
                'label' => 'Manage employees, contracts & HR data',
                'group' => 'Masters & Control',
                'roles' => ['admin'],
            ],
            [
                'name'  => 'manufacturing.manage',
                'label' => 'Manage production orders and runs',
                'group' => 'Production & QC',
                'roles' => ['production_officer', 'qc_officer', 'warehouse_officer'],
            ],
            [
                'name'  => 'manufacturing.qc',
                'label' => 'Approve production QC',
                'group' => 'Production & QC',
                'roles' => ['qc_officer'],
            ],
            [
                'name'  => 'sales.manage',
                'label' => 'Manage sales orders and returns',
                'group' => 'Sales & Returns',
                'roles' => ['sales_officer', 'delivery_coordinator', 'warehouse_officer', 'accounts_officer'],
            ],
            [
                'name'  => 'logistics.shipments',
                'label' => 'Manage courier, air, sea and DDP shipments',
                'group' => 'Logistics',
                'roles' => ['sales_officer', 'delivery_coordinator', 'warehouse_officer', 'accounts_officer'],
            ],
            [
                'name'  => 'logistics.pricing',
                'label' => 'Lock logistics pricing and chargeable weight',
                'group' => 'Logistics',
                'roles' => ['sales_officer', 'accounts_officer'],
            ],
            [
                'name'  => 'logistics.expenses',
                'label' => 'Record and approve shipment expenses',
                'group' => 'Logistics',
                'roles' => ['delivery_coordinator', 'warehouse_officer', 'accounts_officer'],
            ],
            [
                'name'  => 'accounting.manage',
                'label' => 'Manage finance, bills, expenses & accounts',
                'group' => 'Accounting & Finance',
                'roles' => ['accounts_officer', 'purchase_executive'],
            ],
            [
                'name'  => 'reports.view',
                'label' => 'View management reports',
                'group' => 'Reports & Analytics',
                'roles' => ['accounts_officer', 'production_officer', 'sales_officer', 'delivery_coordinator', 'purchase_executive', 'warehouse_officer'],
            ],
            [
                'name'  => 'roles.manage',
                'label' => 'Manage user roles',
                'group' => 'Access Control',
                'roles' => ['admin'],
            ],
            [
                'name'  => 'permissions.manage',
                'label' => 'Manage permissions',
                'group' => 'Access Control',
                'roles' => ['super_admin'],
            ],
            [
                'name'  => 'system.settings',
                'label' => 'Manage system configuration & settings',
                'group' => 'System',
                'roles' => ['admin'],
            ],
            [
                'name'  => 'audit.view',
                'label' => 'View audit logs',
                'group' => 'Audit & Logs',
                'roles' => ['admin'],
            ],
            // Action-level permissions (real-world granular control)
            [
                'name'  => 'purchase.order.create',
                'label' => 'Create purchase orders',
                'group' => 'Purchasing',
                'roles' => ['purchase_executive'],
            ],
            [
                'name'  => 'purchase.order.approve',
                'label' => 'Approve purchase orders',
                'group' => 'Purchasing',
                'roles' => ['purchase_executive', 'accounts_officer'],
            ],
            [
                'name'  => 'inventory.grn.post',
                'label' => 'Post goods receipt (GRN)',
                'group' => 'Inventory & Stock',
                'roles' => ['warehouse_officer'],
            ],
            [
                'name'  => 'inventory.transfer.create',
                'label' => 'Create stock transfers',
                'group' => 'Inventory & Stock',
                'roles' => ['warehouse_officer', 'delivery_coordinator'],
            ],
            [
                'name'  => 'inventory.audit.approve',
                'label' => 'Approve inventory audit variance',
                'group' => 'Inventory & Stock',
                'roles' => ['warehouse_officer', 'accounts_officer'],
            ],
            [
                'name'  => 'manufacturing.run.create',
                'label' => 'Create production runs',
                'group' => 'Production & QC',
                'roles' => ['production_officer'],
            ],
            [
                'name'  => 'manufacturing.qc.approve',
                'label' => 'Approve production QC',
                'group' => 'Production & QC',
                'roles' => ['qc_officer'],
            ],
            [
                'name'  => 'manufacturing.stock.confirm',
                'label' => 'Confirm production stock receipt',
                'group' => 'Production & QC',
                'roles' => ['warehouse_officer'],
            ],
            [
                'name'  => 'sales.order.create',
                'label' => 'Create sales orders',
                'group' => 'Sales & Returns',
                'roles' => ['sales_officer'],
            ],
            [
                'name'  => 'sales.order.approve',
                'label' => 'Approve/confirm sales orders',
                'group' => 'Sales & Returns',
                'roles' => ['sales_officer', 'delivery_coordinator'],
            ],
            [
                'name'  => 'sales.delivery.dispatch',
                'label' => 'Dispatch deliveries and update POD',
                'group' => 'Sales & Returns',
                'roles' => ['delivery_coordinator', 'warehouse_officer'],
            ],
            [
                'name'  => 'finance.invoice.issue',
                'label' => 'Issue customer invoices',
                'group' => 'Accounting & Finance',
                'roles' => ['accounts_officer'],
            ],
            [
                'name'  => 'finance.receipt.post',
                'label' => 'Post customer receipts',
                'group' => 'Accounting & Finance',
                'roles' => ['accounts_officer'],
            ],
            [
                'name'  => 'finance.reconciliation.manage',
                'label' => 'Manage bank reconciliation',
                'group' => 'Accounting & Finance',
                'roles' => ['accounts_officer'],
            ],
        ];

        $seedAllDefaultMappings = ! Permission::query()->exists()
            && ! RolePermission::query()->exists();

        foreach ($definitions as $def) {
            $permission = Permission::updateOrCreate(
                ['name' => $def['name']],
                [
                    'label' => $def['label'],
                    'group' => $def['group'],
                ]
            );

            if ($seedAllDefaultMappings || $permission->wasRecentlyCreated) {
                foreach (array_unique($def['roles']) as $role) {
                    RolePermission::firstOrCreate([
                        'role' => $role,
                        'permission_name' => $def['name'],
                    ]);
                }
            }
        }
    }
}
