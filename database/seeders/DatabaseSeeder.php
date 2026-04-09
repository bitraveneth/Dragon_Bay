<?php

namespace Database\Seeders;

use Database\Seeders\Accounting\BalanceSheetModuleSeeder;
use Database\Seeders\Accounting\BankReconciliationModuleSeeder;
use Database\Seeders\Accounting\CashflowModuleSeeder;
use Database\Seeders\Accounting\ChartOfAccountsModuleSeeder;
use Database\Seeders\Accounting\CustomerInvoicesModuleSeeder;
use Database\Seeders\Accounting\ExpensesModuleSeeder;
use Database\Seeders\Accounting\PayrollModuleSeeder;
use Database\Seeders\Accounting\ProfitAndLossModuleSeeder;
use Database\Seeders\Accounting\SalaryDistributionsModuleSeeder;
use Database\Seeders\Accounting\TaxReportModuleSeeder;
use Database\Seeders\Control\Agents\AgentsModuleSeeder;
use Database\Seeders\Control\Suppliers\SuppliersModuleSeeder;
use Database\Seeders\Control\Employees\EmployeesModuleSeeder;
use Database\Seeders\Control\Products\ProductsModuleSeeder;
use Database\Seeders\Control\SystemSettings\SystemSettingsModuleSeeder;
use Database\Seeders\Control\Warehouses\WarehousesModuleSeeder;
use Database\Seeders\Inventory\InventoryModuleSeeder;
use Database\Seeders\Manufacturing\BatchesLotsModuleSeeder;
use Database\Seeders\Manufacturing\BomsModuleSeeder;
use Database\Seeders\Manufacturing\PendingReceiptsModuleSeeder;
use Database\Seeders\Manufacturing\ProductionAnalysisModuleSeeder;
use Database\Seeders\Manufacturing\ProductionOrdersModuleSeeder;
use Database\Seeders\Sales\CommissionReportModuleSeeder;
use Database\Seeders\Sales\CommissionSettlementsModuleSeeder;
use Database\Seeders\Sales\CustomerGiftsModuleSeeder;
use Database\Seeders\Sales\DeliveriesModuleSeeder;
use Database\Seeders\Sales\MarketingCampaignsModuleSeeder;
use Database\Seeders\Sales\PickingListsModuleSeeder;
use Database\Seeders\Sales\ReturnsModuleSeeder;
use Database\Seeders\Sales\SalesOrdersModuleSeeder;
use Database\Seeders\Sales\SalesTargetsModuleSeeder;
use Database\Seeders\Users\UsersModuleSeeder;
use Database\Seeders\MenuStructureSeeder;
use Database\Seeders\RolesSeeder;
use Database\Seeders\Users\PermissionsSeeder;
use Illuminate\Database\Seeder;

/**
 * Master database seeder.
 *
 * Mirrors the sidebar structure:
 * 1. Control (masters & settings)
 * 2. Manufacturing
 * 3. Inventory (core operations)
 * 4. Sales
 * 5. Accounting
 * 6. Users / roles
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Control (masters & settings)
            ProductsModuleSeeder::class,
            EmployeesModuleSeeder::class,
            WarehousesModuleSeeder::class,
            AgentsModuleSeeder::class,
            SuppliersModuleSeeder::class,
            SystemSettingsModuleSeeder::class,

            // 2. Manufacturing
            BomsModuleSeeder::class,
            BatchesLotsModuleSeeder::class,
            PendingReceiptsModuleSeeder::class,
            ProductionOrdersModuleSeeder::class,
            ProductionAnalysisModuleSeeder::class,

            // 3. Inventory (core operations)
            InventoryModuleSeeder::class,

            // 4. Sales
            SalesOrdersModuleSeeder::class,
            SalesTargetsModuleSeeder::class,
            PickingListsModuleSeeder::class,
            DeliveriesModuleSeeder::class,
            ReturnsModuleSeeder::class,
            CustomerGiftsModuleSeeder::class,
            MarketingCampaignsModuleSeeder::class,
            CommissionReportModuleSeeder::class,
            CommissionSettlementsModuleSeeder::class,

            // 5. Accounting
            ChartOfAccountsModuleSeeder::class,
            CustomerInvoicesModuleSeeder::class,
            ExpensesModuleSeeder::class,
            SalaryDistributionsModuleSeeder::class,
            PayrollModuleSeeder::class,
            BankReconciliationModuleSeeder::class,
            TaxReportModuleSeeder::class,
            ProfitAndLossModuleSeeder::class,
            BalanceSheetModuleSeeder::class,
            CashflowModuleSeeder::class,

            // Extra analytics / demo data so charts look alive.
            //DemoAnalyticsSeeder::class,

            // 6. Users / roles / permissions / menu
            RolesSeeder::class,
            PermissionsSeeder::class,
            UsersModuleSeeder::class,
            MenuStructureSeeder::class,
        ]);
    }
}
