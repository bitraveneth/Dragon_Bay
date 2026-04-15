<?php

use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\BatchController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\DeliveryRouteController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PackagingConversionController;
use App\Http\Controllers\Admin\PackagingTypeController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\AgentPricingController;
use App\Http\Controllers\Admin\AgentLedgerController;
use App\Http\Controllers\Admin\CommissionReportController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\CommissionSettlementController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\SalaryDistributionController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\PurchaseBillController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\GoodsReceiptController;
use App\Http\Controllers\Admin\ProductionController;
use App\Http\Controllers\Admin\StockMovementController;
use App\Http\Controllers\Admin\TaxClassController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\VehicleLoadController;
use App\Http\Controllers\Admin\StockAuditController;
use App\Http\Controllers\Admin\BankReconciliationController;
use App\Http\Controllers\Admin\WarehouseLocationController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\VehicleScheduleController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\EmployeeContractController;
use App\Http\Controllers\Admin\EmployeeAllowanceController;
use App\Http\Controllers\Admin\EmployeeEquipmentController;
use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\Admin\EmployeeLeaveController;
use App\Http\Controllers\Admin\CustomerReturnController;
use App\Http\Controllers\Admin\SupplierReturnController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\EmployeeLocationController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\CustomerGiftController;
use App\Http\Controllers\Admin\BomController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SalesDashboardController;
use App\Http\Controllers\Admin\SalesTargetController;
use App\Http\Controllers\Admin\ManufacturingDashboardController;
use App\Http\Controllers\Admin\AccountingDashboardController;
use App\Http\Controllers\Admin\ReportsDashboardController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\AgentAdvanceController;
use App\Http\Controllers\Admin\ShipmentController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\ClientPortalController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'authenticate'])->middleware('throttle:login');
    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:5,1')->name('password.email');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Backward-compatible home URL
Route::get('/home', function () {
    return redirect()->route('admin.dashboard');
})->middleware('auth')->name('home');

Route::middleware(['auth', 'module.visibility'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::view('help', 'admin.help')->name('help');
    Route::view('client-guide', 'admin.client-guide')->name('client-guide');
    Route::get('settings', [SystemSettingController::class, 'index'])->middleware('perm:system.settings')->name('settings.index');
    Route::patch('settings', [SystemSettingController::class, 'update'])->middleware('perm:system.settings')->name('settings.update');
    Route::post('settings/backups', [SystemSettingController::class, 'createBackup'])->middleware('perm:system.settings')->name('settings.backups.create');
    Route::get('settings/backups/{filename}', [SystemSettingController::class, 'downloadBackup'])->middleware('perm:system.settings')->where('filename', '.*')->name('settings.backups.download');
    Route::post('settings/backups/restore', [SystemSettingController::class, 'restoreBackup'])->middleware('perm:system.settings')->name('settings.backups.restore');
    Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware('perm:audit.view')->name('audit.index');

    // Sales dashboard
    Route::get('sales-dashboard', SalesDashboardController::class)
        ->middleware('perm:sales.manage')
        ->name('sales.dashboard');

    // Accounting dashboard
    Route::get('accounting-dashboard', AccountingDashboardController::class)
        ->middleware('perm:accounting.manage')
        ->name('accounting.dashboard');

    // Reports dashboard
    Route::get('reports-dashboard', ReportsDashboardController::class)
        ->middleware('perm:reports.view')
        ->name('reports.dashboard');

    // Manufacturing dashboard
    Route::get('manufacturing-dashboard', ManufacturingDashboardController::class)
        ->middleware('perm:manufacturing.manage')
        ->name('manufacturing.dashboard');

    // My profile
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');

    // Role manager
    Route::get('roles', [RoleController::class, 'index'])->middleware('perm:roles.manage')->name('roles.index');
    Route::post('roles', [RoleController::class, 'create'])->middleware('perm:roles.manage')->name('roles.create');
    Route::patch('roles/{user}', [RoleController::class, 'update'])->middleware('perm:roles.manage')->name('roles.update');

    // User manager
    Route::get('users', [UserController::class, 'index'])->middleware('perm:system.settings')->name('users.index');
    Route::post('users', [UserController::class, 'store'])->middleware('perm:system.settings')->name('users.store');
    Route::get('users/{user}/access', [UserController::class, 'editAccess'])->middleware('perm:system.settings')->name('users.access.edit');
    Route::patch('users/{user}/access', [UserController::class, 'updateAccess'])->middleware('perm:system.settings')->name('users.access.update');

    // Permission manager (super admin)
    Route::get('permissions', [PermissionController::class, 'index'])->middleware('perm:permissions.manage')->name('permissions.index');
    Route::post('permissions', [PermissionController::class, 'store'])->middleware('perm:permissions.manage')->name('permissions.store');
    Route::post('permissions/roles', [PermissionController::class, 'updateRoles'])->middleware('perm:permissions.manage')->name('permissions.roles.update');
    Route::post('permissions/roles/{role}', [PermissionController::class, 'updateRole'])->middleware('perm:permissions.manage')->name('permissions.roles.update-single');
    Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->middleware('perm:permissions.manage')->name('permissions.destroy');
    Route::post('permissions/groups/rename', [PermissionController::class, 'renameGroup'])->middleware('perm:permissions.manage')->name('permissions.groups.rename');
    Route::post('permissions/groups/delete', [PermissionController::class, 'deleteGroup'])->middleware('perm:permissions.manage')->name('permissions.groups.delete');

    // Menu manager (sidebar groups & items)
    Route::get('menu', [MenuController::class, 'index'])->middleware('perm:permissions.manage')->name('menu.index');
    Route::post('menu/groups', [MenuController::class, 'storeGroup'])->middleware('perm:permissions.manage')->name('menu.groups.store');
    Route::patch('menu/groups/{group}', [MenuController::class, 'updateGroup'])->middleware('perm:permissions.manage')->name('menu.groups.update');
    Route::patch('menu/groups/{group}/toggle', [MenuController::class, 'toggleGroup'])->middleware('perm:permissions.manage')->name('menu.groups.toggle');
    Route::delete('menu/groups/{group}', [MenuController::class, 'deleteGroup'])->middleware('perm:permissions.manage')->name('menu.groups.delete');
    Route::post('menu/groups/{group}/move', [MenuController::class, 'moveGroup'])->middleware('perm:permissions.manage')->name('menu.groups.move');
    Route::post('menu/items', [MenuController::class, 'storeItem'])->middleware('perm:permissions.manage')->name('menu.items.store');
    Route::patch('menu/items/{item}', [MenuController::class, 'updateItem'])->middleware('perm:permissions.manage')->name('menu.items.update');
    Route::patch('menu/items/{item}/toggle', [MenuController::class, 'toggleItem'])->middleware('perm:permissions.manage')->name('menu.items.toggle');
    Route::delete('menu/items/{item}', [MenuController::class, 'deleteItem'])->middleware('perm:permissions.manage')->name('menu.items.delete');
    Route::post('menu/items/{item}/move', [MenuController::class, 'moveItem'])->middleware('perm:permissions.manage')->name('menu.items.move');
    Route::post('menu/items/{item}/move-group', [MenuController::class, 'moveItemGroup'])->middleware('perm:permissions.manage')->name('menu.items.move-group');

    // Finished products catalog
    Route::get('products', [ProductController::class, 'index'])->middleware('perm:control.products')->name('products.index');
    Route::get('products/create', [ProductController::class, 'create'])->middleware('perm:control.products')->name('products.create');
    Route::post('products', [ProductController::class, 'store'])->middleware('perm:control.products')->name('products.store');
    Route::get('products/{product}', [ProductController::class, 'show'])->middleware('perm:control.products')->name('products.show');
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->middleware('perm:control.products')->name('products.edit');
    Route::patch('products/{product}', [ProductController::class, 'update'])->middleware('perm:control.products')->name('products.update');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->middleware('perm:control.products')->name('products.destroy');
    Route::get('products-export', [ProductController::class, 'export'])->middleware('perm:control.products')->name('products.export');
    Route::get('products-price-list', [ProductController::class, 'priceList'])->middleware('perm:control.products')->name('products.prices.index');
    Route::get('products-price-list/{product}', [ProductController::class, 'showPriceList'])->middleware('perm:control.products')->name('products.prices.show');

    // Materials (raw / service / in‑house) – managed separately but stored in products table
    Route::get('materials', [ProductController::class, 'materialsIndex'])->middleware('perm:control.products')->name('materials.index');
    Route::get('materials/create', [ProductController::class, 'materialsCreate'])->middleware('perm:control.products')->name('materials.create');
    Route::post('materials', [ProductController::class, 'store'])->middleware('perm:control.products')->name('materials.store');
    Route::get('materials/{product}/edit', [ProductController::class, 'materialsEdit'])->middleware('perm:control.products')->name('materials.edit');
    Route::patch('materials/{product}', [ProductController::class, 'update'])->middleware('perm:control.products')->name('materials.update');
    Route::delete('materials/{product}', [ProductController::class, 'destroy'])->middleware('perm:control.products')->name('materials.destroy');

    Route::get('packaging', [PackagingTypeController::class, 'index'])->middleware('perm:control.products')->name('packaging.index');
    Route::post('packaging', [PackagingTypeController::class, 'store'])->middleware('perm:control.products')->name('packaging.store');
    Route::get('packaging/{packagingType}/edit', [PackagingTypeController::class, 'edit'])->middleware('perm:control.products')->name('packaging.edit');
    Route::patch('packaging/{packagingType}', [PackagingTypeController::class, 'update'])->middleware('perm:control.products')->name('packaging.update');
    Route::delete('packaging/{packagingType}', [PackagingTypeController::class, 'destroy'])->middleware('perm:control.products')->name('packaging.destroy');
    Route::post('packaging/conversions', [PackagingConversionController::class, 'store'])->middleware('perm:control.products')->name('packaging.conversions.store');
    Route::delete('packaging/conversions/{conversion}', [PackagingConversionController::class, 'destroy'])->middleware('perm:control.products')->name('packaging.conversions.destroy');

    Route::get('boms', [BomController::class, 'index'])->middleware('perm:manufacturing.manage')->name('boms.index');
    Route::get('boms/create', [BomController::class, 'create'])->middleware('perm:manufacturing.manage')->name('boms.create');
    Route::post('boms', [BomController::class, 'store'])->middleware('perm:manufacturing.manage')->name('boms.store');
    Route::get('boms/{bom}/edit', [BomController::class, 'edit'])->middleware('perm:manufacturing.manage')->name('boms.edit');
    Route::patch('boms/{bom}', [BomController::class, 'update'])->middleware('perm:manufacturing.manage')->name('boms.update');
    Route::delete('boms/{bom}', [BomController::class, 'destroy'])->middleware('perm:manufacturing.manage')->name('boms.destroy');

    Route::get('tax-classes', [TaxClassController::class, 'index'])->middleware('perm:control.products')->name('tax-classes.index');
    Route::post('tax-classes', [TaxClassController::class, 'store'])->middleware('perm:control.products')->name('tax-classes.store');
    Route::get('tax-classes/{taxClass}/edit', [TaxClassController::class, 'edit'])->middleware('perm:control.products')->name('tax-classes.edit');
    Route::patch('tax-classes/{taxClass}', [TaxClassController::class, 'update'])->middleware('perm:control.products')->name('tax-classes.update');
    Route::delete('tax-classes/{taxClass}', [TaxClassController::class, 'destroy'])->middleware('perm:control.products')->name('tax-classes.destroy');

    Route::get('vehicles', [VehicleController::class, 'index'])->middleware('perm:control.warehouses')->name('vehicles.index');
    Route::get('vehicles/create', [VehicleController::class, 'create'])->middleware('perm:control.warehouses')->name('vehicles.create');
    Route::post('vehicles', [VehicleController::class, 'store'])->middleware('perm:control.warehouses')->name('vehicles.store');

    Route::get('employees', [EmployeeController::class, 'index'])->middleware('perm:control.employees')->name('employees.index');
    Route::get('employees/create', [EmployeeController::class, 'create'])->middleware('perm:control.employees')->name('employees.create');
    Route::post('employees', [EmployeeController::class, 'store'])->middleware('perm:control.employees')->name('employees.store');
    Route::get('employees/{employee}', [EmployeeController::class, 'show'])->middleware('perm:control.employees')->name('employees.show');
    Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->middleware('perm:control.employees')->name('employees.edit');
    Route::patch('employees/{employee}', [EmployeeController::class, 'update'])->middleware('perm:control.employees')->name('employees.update');
    Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->middleware('perm:control.employees')->name('employees.destroy');

    // Create login account for an employee
    Route::get('employees/{employee}/user', [EmployeeController::class, 'createUser'])->middleware(['perm:control.employees', 'perm:system.settings'])->name('employees.user.create');
    Route::post('employees/{employee}/user', [EmployeeController::class, 'storeUser'])->middleware(['perm:control.employees', 'perm:system.settings'])->name('employees.user.store');

    Route::get('contracts', [EmployeeContractController::class, 'all'])->middleware('perm:control.employees')->name('contracts.index');
    Route::get('contracts/create', [EmployeeContractController::class, 'createGlobal'])->middleware('perm:control.employees')->name('contracts.create');
    Route::post('contracts', [EmployeeContractController::class, 'storeGlobal'])->middleware('perm:control.employees')->name('contracts.store');
    Route::get('employees/{employee}/contracts', [EmployeeContractController::class, 'index'])->middleware('perm:control.employees')->name('employees.contracts.index');
    Route::get('employees/{employee}/contracts/create', [EmployeeContractController::class, 'create'])->middleware('perm:control.employees')->name('employees.contracts.create');
    Route::post('employees/{employee}/contracts', [EmployeeContractController::class, 'store'])->middleware('perm:control.employees')->name('employees.contracts.store');
    Route::get('employees/{employee}/contracts/{contract}/edit', [EmployeeContractController::class, 'edit'])->middleware('perm:control.employees')->name('employees.contracts.edit');
    Route::patch('employees/{employee}/contracts/{contract}', [EmployeeContractController::class, 'update'])->middleware('perm:control.employees')->name('employees.contracts.update');
    Route::delete('employees/{employee}/contracts/{contract}', [EmployeeContractController::class, 'destroy'])->middleware('perm:control.employees')->name('employees.contracts.destroy');

    Route::get('allowances', [EmployeeAllowanceController::class, 'all'])->middleware('perm:control.employees')->name('allowances.index');
    Route::get('allowances/create', [EmployeeAllowanceController::class, 'createGlobal'])->middleware('perm:control.employees')->name('allowances.create');
    Route::post('allowances', [EmployeeAllowanceController::class, 'storeGlobal'])->middleware('perm:control.employees')->name('allowances.store');
    Route::get('employees/{employee}/allowances', [EmployeeAllowanceController::class, 'index'])->middleware('perm:control.employees')->name('employees.allowances.index');
    Route::get('employees/{employee}/allowances/create', [EmployeeAllowanceController::class, 'create'])->middleware('perm:control.employees')->name('employees.allowances.create');
    Route::post('employees/{employee}/allowances', [EmployeeAllowanceController::class, 'store'])->middleware('perm:control.employees')->name('employees.allowances.store');
    Route::get('employees/{employee}/allowances/{allowance}/edit', [EmployeeAllowanceController::class, 'edit'])->middleware('perm:control.employees')->name('employees.allowances.edit');
    Route::patch('employees/{employee}/allowances/{allowance}', [EmployeeAllowanceController::class, 'update'])->middleware('perm:control.employees')->name('employees.allowances.update');
    Route::delete('employees/{employee}/allowances/{allowance}', [EmployeeAllowanceController::class, 'destroy'])->middleware('perm:control.employees')->name('employees.allowances.destroy');

    Route::get('equipment', [EmployeeEquipmentController::class, 'all'])->middleware('perm:control.employees')->name('equipment.index');
    Route::get('equipment/create', [EmployeeEquipmentController::class, 'createGlobal'])->middleware('perm:control.employees')->name('equipment.create');
    Route::post('equipment', [EmployeeEquipmentController::class, 'storeGlobal'])->middleware('perm:control.employees')->name('equipment.store');
    Route::get('employees/{employee}/equipment', [EmployeeEquipmentController::class, 'index'])->middleware('perm:control.employees')->name('employees.equipment.index');
    Route::get('employees/{employee}/equipment/create', [EmployeeEquipmentController::class, 'create'])->middleware('perm:control.employees')->name('employees.equipment.create');
    Route::post('employees/{employee}/equipment', [EmployeeEquipmentController::class, 'store'])->middleware('perm:control.employees')->name('employees.equipment.store');
    Route::get('employees/{employee}/equipment/{equipment}/edit', [EmployeeEquipmentController::class, 'edit'])->middleware('perm:control.employees')->name('employees.equipment.edit');
    Route::patch('employees/{employee}/equipment/{equipment}', [EmployeeEquipmentController::class, 'update'])->middleware('perm:control.employees')->name('employees.equipment.update');
    Route::delete('employees/{employee}/equipment/{equipment}', [EmployeeEquipmentController::class, 'destroy'])->middleware('perm:control.employees')->name('employees.equipment.destroy');

    Route::get('leaves', [EmployeeLeaveController::class, 'all'])->middleware('perm:control.employees')->name('leaves.index');
    Route::get('leaves/create', [EmployeeLeaveController::class, 'createGlobal'])->middleware('perm:control.employees')->name('leaves.create');
    Route::post('leaves', [EmployeeLeaveController::class, 'storeGlobal'])->middleware('perm:control.employees')->name('leaves.store');
    Route::get('employees/{employee}/leaves', [EmployeeLeaveController::class, 'index'])->middleware('perm:control.employees')->name('employees.leaves.index');
    Route::get('employees/{employee}/leaves/create', [EmployeeLeaveController::class, 'create'])->middleware('perm:control.employees')->name('employees.leaves.create');
    Route::post('employees/{employee}/leaves', [EmployeeLeaveController::class, 'store'])->middleware('perm:control.employees')->name('employees.leaves.store');
    Route::get('employees/{employee}/leaves/{leave}/edit', [EmployeeLeaveController::class, 'edit'])->middleware('perm:control.employees')->name('employees.leaves.edit');
    Route::patch('employees/{employee}/leaves/{leave}', [EmployeeLeaveController::class, 'update'])->middleware('perm:control.employees')->name('employees.leaves.update');
    Route::delete('employees/{employee}/leaves/{leave}', [EmployeeLeaveController::class, 'destroy'])->middleware('perm:control.employees')->name('employees.leaves.destroy');

    Route::get('locations', [EmployeeLocationController::class, 'all'])->middleware('perm:control.employees')->name('locations.index');
    Route::get('locations/create', [EmployeeLocationController::class, 'createGlobal'])->middleware('perm:control.employees')->name('locations.create');
    Route::post('locations', [EmployeeLocationController::class, 'storeGlobal'])->middleware('perm:control.employees')->name('locations.store');
    Route::get('employees/{employee}/locations', [EmployeeLocationController::class, 'index'])->middleware('perm:control.employees')->name('employees.locations.index');
    Route::get('employees/{employee}/locations/create', [EmployeeLocationController::class, 'create'])->middleware('perm:control.employees')->name('employees.locations.create');
    Route::post('employees/{employee}/locations', [EmployeeLocationController::class, 'store'])->middleware('perm:control.employees')->name('employees.locations.store');
    Route::get('employees/{employee}/locations/{location}/edit', [EmployeeLocationController::class, 'edit'])->middleware('perm:control.employees')->name('employees.locations.edit');
    Route::patch('employees/{employee}/locations/{location}', [EmployeeLocationController::class, 'update'])->middleware('perm:control.employees')->name('employees.locations.update');
    Route::delete('employees/{employee}/locations/{location}', [EmployeeLocationController::class, 'destroy'])->middleware('perm:control.employees')->name('employees.locations.destroy');

    Route::get('badges', [BadgeController::class, 'index'])->middleware('perm:control.employees')->name('badges.index');
    Route::get('badges/create', [BadgeController::class, 'create'])->middleware('perm:control.employees')->name('badges.create');
    Route::post('badges', [BadgeController::class, 'store'])->middleware('perm:control.employees')->name('badges.store');
    Route::get('badges/{badge}/edit', [BadgeController::class, 'edit'])->middleware('perm:control.employees')->name('badges.edit');
    Route::patch('badges/{badge}', [BadgeController::class, 'update'])->middleware('perm:control.employees')->name('badges.update');
    Route::delete('badges/{badge}', [BadgeController::class, 'destroy'])->middleware('perm:control.employees')->name('badges.destroy');
    Route::get('badges/{badge}/grant', [BadgeController::class, 'grantFromBadgeForm'])->middleware('perm:control.employees')->name('badges.grant-form');
    Route::post('badges/{badge}/grant', [BadgeController::class, 'grantFromBadge'])->middleware('perm:control.employees')->name('badges.grant');

    Route::get('employees/{employee}/badges/grant', [BadgeController::class, 'grantForm'])->middleware('perm:control.employees')->name('employees.badges.grant-form');
    Route::post('employees/{employee}/badges/grant', [BadgeController::class, 'grant'])->middleware('perm:control.employees')->name('employees.badges.grant');

    Route::get('agents', [AgentController::class, 'index'])->middleware('perm:control.agents')->name('agents.index');
    Route::get('clients', [ClientController::class, 'index'])->middleware('perm:control.agents')->name('clients.index');
    Route::get('agents/create', [AgentController::class, 'create'])->middleware('perm:control.agents')->name('agents.create');
    Route::get('clients/create', [ClientController::class, 'create'])->middleware('perm:control.agents')->name('clients.create');
    Route::post('agents', [AgentController::class, 'store'])->middleware('perm:control.agents')->name('agents.store');
    Route::post('clients', [ClientController::class, 'store'])->middleware('perm:control.agents')->name('clients.store');
    Route::get('agents/{agent}', [AgentController::class, 'show'])->middleware('perm:control.agents')->name('agents.show');
    Route::get('clients/{client}', [ClientController::class, 'show'])->middleware('perm:control.agents')->name('clients.show');
    Route::get('agents/{agent}/edit', [AgentController::class, 'edit'])->middleware('perm:control.agents')->name('agents.edit');
    Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->middleware('perm:control.agents')->name('clients.edit');
    Route::patch('agents/{agent}', [AgentController::class, 'update'])->middleware('perm:control.agents')->name('agents.update');
    Route::patch('clients/{client}', [ClientController::class, 'update'])->middleware('perm:control.agents')->name('clients.update');
    Route::delete('agents/{agent}', [AgentController::class, 'destroy'])->middleware('perm:control.agents')->name('agents.destroy');
    Route::delete('clients/{client}', [ClientController::class, 'destroy'])->middleware('perm:control.agents')->name('clients.destroy');
    Route::get('agents/{agent}/pricing', [AgentPricingController::class, 'edit'])->middleware('perm:control.agents')->name('agents.pricing.edit');
    Route::patch('agents/{agent}/pricing', [AgentPricingController::class, 'update'])->middleware('perm:control.agents')->name('agents.pricing.update');
    Route::get('agents/{agent}/ledger', [AgentLedgerController::class, 'show'])->middleware('perm:control.agents')->name('agents.ledger.show');
    Route::get('sales-targets', [SalesTargetController::class, 'index'])->middleware('perm:sales.manage')->name('sales-targets.index');
    Route::get('sales-targets/create', [SalesTargetController::class, 'create'])->middleware('perm:sales.manage')->name('sales-targets.create');
    Route::post('sales-targets', [SalesTargetController::class, 'store'])->middleware('perm:sales.manage')->name('sales-targets.store');
    Route::get('sales-targets/{salesTarget}/edit', [SalesTargetController::class, 'edit'])->middleware('perm:sales.manage')->name('sales-targets.edit');
    Route::patch('sales-targets/{salesTarget}', [SalesTargetController::class, 'update'])->middleware('perm:sales.manage')->name('sales-targets.update');
    Route::get('commissions', [CommissionReportController::class, 'index'])->middleware('perm:control.agents')->name('commissions.index');
    Route::get('commissions/summary', [CommissionReportController::class, 'index'])->middleware('perm:control.agents')->name('commissions.summary');
    Route::get('commissions/export', [CommissionReportController::class, 'export'])->middleware('perm:control.agents')->name('commissions.export');
    Route::get('commission-rules', [CommissionReportController::class, 'rules'])->middleware('perm:control.agents')->name('commissions.rules');
    Route::get('settlements', [CommissionSettlementController::class, 'index'])->middleware('perm:control.agents')->name('settlements.index');
    Route::post('settlements/generate', [CommissionSettlementController::class, 'generate'])->middleware('perm:control.agents')->name('settlements.generate');
    Route::patch('settlements/{settlement}', [CommissionSettlementController::class, 'updateStatus'])->middleware('perm:accounting.manage')->name('settlements.update-status');

    Route::get('orders', [OrderController::class, 'index'])->middleware('perm:sales.manage')->name('orders.index');
    Route::get('orders-picking', [OrderController::class, 'pickingOverview'])->middleware('perm:sales.manage')->name('orders.picking-overview');
    Route::get('orders/create', [OrderController::class, 'create'])->middleware('perm:sales.manage')->name('orders.create');
    Route::post('orders', [OrderController::class, 'store'])->middleware('perm:sales.manage')->name('orders.store');
    Route::get('orders/{order}', [OrderController::class, 'show'])->middleware('perm:sales.manage')->name('orders.show');
    Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->middleware('perm:sales.manage')->name('orders.edit');
    Route::patch('orders/{order}', [OrderController::class, 'update'])->middleware('perm:sales.manage')->name('orders.update');
    Route::get('orders/{order}/picking-list', [OrderController::class, 'pickingList'])->middleware('perm:sales.manage')->name('orders.picking-list');
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->middleware('perm:sales.manage')->name('orders.status.update');
    Route::delete('orders/{order}', [OrderController::class, 'destroy'])->middleware('perm:sales.manage')->name('orders.destroy');
    Route::post('orders/{order}/invoice', [FinanceController::class, 'createFromOrder'])->middleware('perm:sales.manage')->name('orders.invoice');

    Route::get('shipments', [ShipmentController::class, 'index'])->middleware('perm:logistics.shipments')->name('shipments.index');
    Route::get('shipments/create', [ShipmentController::class, 'create'])->middleware('perm:logistics.shipments')->name('shipments.create');
    Route::post('shipments', [ShipmentController::class, 'store'])->middleware('perm:logistics.shipments')->name('shipments.store');
    Route::get('shipments/{shipment}', [ShipmentController::class, 'show'])->middleware('perm:logistics.shipments')->name('shipments.show');
    Route::patch('shipments/{shipment}/status', [ShipmentController::class, 'updateStatus'])->middleware('perm:logistics.shipments')->name('shipments.status.update');
    Route::post('shipments/{shipment}/packages', [ShipmentController::class, 'storePackage'])->middleware('perm:logistics.shipments')->name('shipments.packages.store');
    Route::post('shipments/{shipment}/legs', [ShipmentController::class, 'storeLeg'])->middleware('perm:logistics.shipments')->name('shipments.legs.store');
    Route::post('shipments/{shipment}/pod', [ShipmentController::class, 'storePod'])->middleware('perm:logistics.shipments')->name('shipments.pod.store');
    Route::post('shipments/{shipment}/expenses', [ShipmentController::class, 'storeExpense'])->middleware('perm:logistics.expenses')->name('shipments.expenses.store');
    Route::post('shipments/{shipment}/expenses/{expense}/approve', [ShipmentController::class, 'approveExpense'])->middleware('perm:logistics.expenses')->name('shipments.expenses.approve');
    Route::post('shipments/{shipment}/pricing/lock', [ShipmentController::class, 'lockPricing'])->middleware('perm:logistics.pricing')->name('shipments.pricing.lock');
    Route::post('shipments/{shipment}/invoice', [ShipmentController::class, 'createInvoice'])->middleware('perm:finance.invoice.issue')->name('shipments.invoice');

    Route::get('deliveries', [DeliveryController::class, 'index'])->middleware('perm:control.warehouses')->name('deliveries.index');
    Route::get('deliveries/pod', [DeliveryController::class, 'podIndex'])->middleware('perm:control.warehouses')->name('deliveries.pod-index');
    Route::get('deliveries/packing-slips', [DeliveryController::class, 'packingIndex'])->middleware('perm:control.warehouses')->name('deliveries.packing-index');
    Route::get('deliveries/create', [DeliveryController::class, 'create'])->middleware('perm:control.warehouses')->name('deliveries.create');
    Route::post('deliveries', [DeliveryController::class, 'store'])->middleware('perm:control.warehouses')->name('deliveries.store');
    Route::get('deliveries/{delivery}/edit', [DeliveryController::class, 'edit'])->middleware('perm:control.warehouses')->name('deliveries.edit');
    Route::post('deliveries/optimize', [DeliveryController::class, 'optimize'])->middleware('perm:control.warehouses')->name('deliveries.optimize');
    Route::patch('deliveries/{delivery}', [DeliveryController::class, 'update'])->middleware('perm:control.warehouses')->name('deliveries.update');
    Route::delete('deliveries/{delivery}', [DeliveryController::class, 'destroy'])->middleware('perm:control.warehouses')->name('deliveries.destroy');
    Route::get('deliveries/{delivery}/packing-slip', [DeliveryController::class, 'packingSlip'])->middleware('perm:control.warehouses')->name('deliveries.packing-slip');
    Route::get('delivery-routes', [DeliveryRouteController::class, 'index'])->middleware('perm:control.warehouses')->name('delivery-routes.index');
    Route::post('delivery-routes', [DeliveryRouteController::class, 'store'])->middleware('perm:control.warehouses')->name('delivery-routes.store');
    Route::get('vehicle-load', [VehicleLoadController::class, 'index'])->middleware('perm:control.warehouses')->name('vehicle-load.index');
    Route::get('vehicle-schedule', [VehicleScheduleController::class, 'index'])->middleware('perm:control.warehouses')->name('vehicle-schedule.index');
    Route::post('vehicle-schedule', [VehicleScheduleController::class, 'store'])->middleware('perm:control.warehouses')->name('vehicle-schedule.store');

    Route::get('stock/movements', [StockMovementController::class, 'index'])->middleware('perm:inventory.manage')->name('stock.movements');
    Route::get('stock/transfers', [StockMovementController::class, 'create'])->middleware('perm:inventory.manage')->name('stock.transfers');
    Route::post('stock/transfers', [StockMovementController::class, 'store'])->middleware('perm:inventory.manage')->name('stock.transfers.store');
    Route::get('stock/write-off', [StockMovementController::class, 'writeOffForm'])->middleware('perm:inventory.manage')->name('stock.writeoff');
    Route::post('stock/write-off', [StockMovementController::class, 'writeOffStore'])->middleware('perm:inventory.manage')->name('stock.writeoff.store');
    Route::post('stock/entries/{entry}/write-off', [StockMovementController::class, 'writeOffEntry'])->middleware('perm:inventory.manage')->name('stock.entries.writeoff');
    Route::get('stock/audit', [StockAuditController::class, 'index'])->middleware('perm:inventory.manage')->name('stock.audit');
    Route::post('stock/audit', [StockAuditController::class, 'store'])->middleware('perm:inventory.manage')->name('stock.audit.store');
    Route::delete('stock/audit/{audit}', [StockAuditController::class, 'destroy'])->middleware('perm:inventory.manage')->name('stock.audit.destroy');

    Route::get('inventory', [InventoryController::class, 'index'])->middleware('perm:inventory.manage')->name('inventory.index');
    Route::get('inventory/materials', [InventoryController::class, 'materials'])->middleware('perm:inventory.manage')->name('inventory.materials');

    Route::get('notifications', [AdminController::class, 'notifications'])->name('notifications.index');
    Route::get('notifications/header-data', [AdminController::class, 'headerNotifications'])->name('notifications.header-data');
    Route::post('notifications/mark-all-read', [AdminController::class, 'markAllNotificationsRead'])->name('notifications.mark-all-read');
    Route::post('notifications/mark-all-unread', [AdminController::class, 'markAllNotificationsUnread'])->name('notifications.mark-all-unread');
    Route::post('notifications/{notification}/mark-read', [AdminController::class, 'markNotificationRead'])->name('notifications.mark-read');
    Route::post('notifications/{notification}/mark-unread', [AdminController::class, 'markNotificationUnread'])->name('notifications.mark-unread');

    Route::get('warehouses', [WarehouseController::class, 'index'])->middleware('perm:control.warehouses')->name('warehouses.index');
    Route::get('warehouses/create', [WarehouseController::class, 'create'])->middleware('perm:control.warehouses')->name('warehouses.create');
    Route::post('warehouses', [WarehouseController::class, 'store'])->middleware('perm:control.warehouses')->name('warehouses.store');
    Route::get('warehouses/{warehouse}', [WarehouseController::class, 'show'])->middleware('perm:control.warehouses')->name('warehouses.show');
    Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->middleware('perm:control.warehouses')->name('warehouses.edit');
    Route::patch('warehouses/{warehouse}', [WarehouseController::class, 'update'])->middleware('perm:control.warehouses')->name('warehouses.update');
    Route::post('warehouses/{warehouse}/clear-stock', [WarehouseController::class, 'clearStock'])->middleware('perm:control.warehouses')->name('warehouses.clear-stock');
    Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->middleware('perm:control.warehouses')->name('warehouses.destroy');
    Route::get('warehouse-locations', [WarehouseLocationController::class, 'index'])->middleware('perm:control.warehouses')->name('warehouse-locations.index');
    Route::post('warehouse-locations', [WarehouseLocationController::class, 'store'])->middleware('perm:control.warehouses')->name('warehouse-locations.store');
    Route::delete('warehouse-locations/{location}', [WarehouseLocationController::class, 'destroy'])->middleware('perm:control.warehouses')->name('warehouse-locations.destroy');

    Route::get('suppliers', [SupplierController::class, 'index'])->middleware('perm:control.suppliers')->name('suppliers.index');
    Route::get('suppliers/create', [SupplierController::class, 'create'])->middleware('perm:control.suppliers')->name('suppliers.create');
    Route::post('suppliers', [SupplierController::class, 'store'])->middleware('perm:control.suppliers')->name('suppliers.store');

    Route::get('production', [ProductionController::class, 'index'])->middleware('perm:manufacturing.manage')->name('production.index');
    Route::get('production/create', [ProductionController::class, 'create'])->middleware('perm:manufacturing.manage')->name('production.create');
    Route::post('production', [ProductionController::class, 'store'])->middleware('perm:manufacturing.manage')->name('production.store');
    Route::get('production/pending-receipts', [ProductionController::class, 'pendingReceipts'])->middleware('perm:manufacturing.manage')->name('production.pending-receipts');
    Route::get('production/order-number', [ProductionController::class, 'orderNumber'])->middleware('perm:manufacturing.manage')->name('production.order-number');
    Route::get('production/{production}', [ProductionController::class, 'show'])->middleware('perm:manufacturing.manage')->name('production.show');
    Route::get('production/{production}/edit', [ProductionController::class, 'edit'])->middleware('perm:manufacturing.manage')->name('production.edit');
    Route::patch('production/{production}', [ProductionController::class, 'update'])->middleware('perm:manufacturing.manage')->name('production.update');
    Route::post('production/{production}/confirm-stock', [ProductionController::class, 'confirmStock'])->middleware('perm:manufacturing.manage')->name('production.confirm-stock');
    Route::delete('production/{production}', [ProductionController::class, 'destroy'])->middleware('perm:manufacturing.manage')->name('production.destroy');

    Route::get('finance', [FinanceController::class, 'index'])->middleware('perm:accounting.manage')->name('finance.index');
    Route::post('finance/{invoice}/receipt', [FinanceController::class, 'storeReceipt'])->middleware('perm:accounting.manage')->name('finance.receipts.store');
    Route::get('finance/{invoice}/credit-note', [FinanceController::class, 'showCreditNoteForm'])->middleware('perm:accounting.manage')->name('finance.credit-notes.create');
    Route::post('finance/{invoice}/credit-note', [FinanceController::class, 'storeCreditNote'])->middleware('perm:accounting.manage')->name('finance.credit-notes.store');
    Route::get('finance/{invoice}/pdf', [FinanceController::class, 'downloadPdf'])->middleware('perm:accounting.manage')->name('finance.pdf');
    Route::get('finance/reconciliation', [BankReconciliationController::class, 'index'])->middleware('perm:accounting.manage')->name('finance.reconciliation');
    Route::post('finance/reconciliation', [BankReconciliationController::class, 'update'])->middleware('perm:accounting.manage')->name('finance.reconciliation.update');
    Route::get('reports/pl', [ReportController::class, 'profitAndLoss'])->middleware('perm:reports.view')->name('reports.pl');
    Route::get('reports/vat', [ReportController::class, 'vat'])->middleware('perm:reports.view')->name('reports.vat');
    Route::get('reports/bs', [ReportController::class, 'balanceSheet'])->middleware('perm:reports.view')->name('reports.bs');
    Route::get('reports/cashflow', [ReportController::class, 'cashflow'])->middleware('perm:reports.view')->name('reports.cashflow');
    Route::get('reports/agents', [ReportController::class, 'agentPerformance'])->middleware('perm:reports.view')->name('reports.agents');
    Route::get('reports/clients/outstanding', [ReportController::class, 'clientOutstanding'])->middleware('perm:reports.view')->name('reports.clients.outstanding');
    Route::get('reports/shipments/profitability', [ReportController::class, 'shipmentProfitability'])->middleware('perm:reports.view')->name('reports.shipments.profitability');
    Route::get('reports/shipments/weight-usage', [ReportController::class, 'weightUsage'])->middleware('perm:reports.view')->name('reports.shipments.weight-usage');
    Route::get('reports/shipments/mode-performance', [ReportController::class, 'modePerformance'])->middleware('perm:reports.view')->name('reports.shipments.mode-performance');
    Route::get('reports/shipments/{report}/export/{format}', [ReportController::class, 'exportShipmentReport'])->middleware('perm:reports.view')->name('reports.shipments.export');
    Route::get('reports/production', [ReportController::class, 'productionSummary'])->middleware('perm:reports.view')->name('reports.production');
    Route::get('reports/payroll', [ReportController::class, 'payrollSummary'])->middleware('perm:reports.view')->name('reports.payroll');
    Route::get('salary-distributions', [SalaryDistributionController::class, 'index'])->middleware('perm:accounting.manage')->name('salary-distributions.index');
    Route::get('salary-distributions/create', [SalaryDistributionController::class, 'create'])->middleware('perm:accounting.manage')->name('salary-distributions.create');
    Route::post('salary-distributions', [SalaryDistributionController::class, 'store'])->middleware('perm:accounting.manage')->name('salary-distributions.store');
    Route::get('salary-distributions/{salaryDistribution}/edit', [SalaryDistributionController::class, 'edit'])->middleware('perm:accounting.manage')->name('salary-distributions.edit');
    Route::patch('salary-distributions/{salaryDistribution}', [SalaryDistributionController::class, 'update'])->middleware('perm:accounting.manage')->name('salary-distributions.update');
    Route::delete('salary-distributions/{salaryDistribution}', [SalaryDistributionController::class, 'destroy'])->middleware('perm:accounting.manage')->name('salary-distributions.destroy');
    Route::get('finance/{invoice}', [FinanceController::class, 'show'])->middleware('perm:accounting.manage')->name('finance.show');
    Route::patch('finance/{invoice}/withholding', [FinanceController::class, 'updateWithholding'])->middleware('perm:accounting.manage')->name('finance.withholding.update');
    Route::delete('finance/{invoice}', [FinanceController::class, 'destroy'])->middleware('perm:accounting.manage')->name('finance.destroy');
    Route::delete('finance/receipts/{receipt}', [FinanceController::class, 'destroyReceipt'])->middleware('perm:accounting.manage')->name('finance.receipts.destroy');
    Route::delete('finance/credit-notes/{creditNote}', [FinanceController::class, 'destroyCreditNote'])->middleware('perm:accounting.manage')->name('finance.credit-notes.destroy');
    Route::get('expenses', [ExpenseController::class, 'index'])->middleware('perm:accounting.manage')->name('expenses.index');
    Route::get('expenses/create', [ExpenseController::class, 'create'])->middleware('perm:accounting.manage')->name('expenses.create');
    Route::post('expenses', [ExpenseController::class, 'store'])->middleware('perm:accounting.manage')->name('expenses.store');
    Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])->middleware('perm:accounting.manage')->name('expenses.edit');
    Route::patch('expenses/{expense}', [ExpenseController::class, 'update'])->middleware('perm:accounting.manage')->name('expenses.update');
    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->middleware('perm:accounting.manage')->name('expenses.destroy');
    Route::get('accounts', [AccountController::class, 'index'])->middleware('perm:accounting.manage')->name('accounts.index');
    Route::get('accounts/create', [AccountController::class, 'create'])->middleware('perm:accounting.manage')->name('accounts.create');
    Route::post('accounts', [AccountController::class, 'store'])->middleware('perm:accounting.manage')->name('accounts.store');
    Route::get('accounts/{account}/edit', [AccountController::class, 'edit'])->middleware('perm:accounting.manage')->name('accounts.edit');
    Route::patch('accounts/{account}', [AccountController::class, 'update'])->middleware('perm:accounting.manage')->name('accounts.update');
    Route::delete('accounts/{account}', [AccountController::class, 'destroy'])->middleware('perm:accounting.manage')->name('accounts.destroy');
    Route::get('agent-advances', [AgentAdvanceController::class, 'index'])->middleware('perm:accounting.manage')->name('agent-advances.index');
    Route::get('agent-advances/create', [AgentAdvanceController::class, 'create'])->middleware('perm:accounting.manage')->name('agent-advances.create');
    Route::post('agent-advances', [AgentAdvanceController::class, 'store'])->middleware('perm:accounting.manage')->name('agent-advances.store');

    Route::get('bills', [PurchaseBillController::class, 'index'])->middleware('perm:accounting.manage')->name('bills.index');
    Route::get('bills/create', [PurchaseBillController::class, 'create'])->middleware('perm:accounting.manage')->name('bills.create');
    Route::post('bills', [PurchaseBillController::class, 'store'])->middleware('perm:accounting.manage')->name('bills.store');
    Route::get('bills/{bill}/edit', [PurchaseBillController::class, 'edit'])->middleware('perm:accounting.manage')->name('bills.edit');
    Route::put('bills/{bill}', [PurchaseBillController::class, 'update'])->middleware('perm:accounting.manage')->name('bills.update');
    Route::delete('bills/{bill}', [PurchaseBillController::class, 'destroy'])->middleware('perm:accounting.manage')->name('bills.destroy');
    Route::post('bills/{bill}/pay', [PurchaseBillController::class, 'storePayment'])->middleware('perm:accounting.manage')->name('bills.pay');

    Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->middleware('perm:control.suppliers')->name('purchase-orders.index');
    Route::get('purchase-orders/create', [PurchaseOrderController::class, 'create'])->middleware('perm:control.suppliers')->name('purchase-orders.create');
    Route::post('purchase-orders', [PurchaseOrderController::class, 'store'])->middleware('perm:control.suppliers')->name('purchase-orders.store');
    Route::get('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->middleware('perm:control.suppliers')->name('purchase-orders.show');
    Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->middleware('perm:control.suppliers')->name('purchase-orders.approve');

    Route::get('goods-receipts', [GoodsReceiptController::class, 'index'])->middleware('perm:inventory.manage')->name('goods-receipts.index');
    Route::get('goods-receipts/create', [GoodsReceiptController::class, 'create'])->middleware('perm:inventory.manage')->name('goods-receipts.create');
    Route::post('goods-receipts', [GoodsReceiptController::class, 'store'])->middleware('perm:inventory.manage')->name('goods-receipts.store');
    Route::get('goods-receipts/{goodsReceipt}', [GoodsReceiptController::class, 'show'])->middleware('perm:inventory.manage')->name('goods-receipts.show');
    Route::post('goods-receipts/{goodsReceipt}/reverse', [GoodsReceiptController::class, 'reverse'])->middleware('perm:inventory.manage')->name('goods-receipts.reverse');

    Route::get('batches', [BatchController::class, 'index'])->middleware('perm:manufacturing.manage')->name('batches.index');
    Route::post('batches', [BatchController::class, 'store'])->middleware('perm:manufacturing.manage')->name('batches.store');
    Route::get('batches/{batch}', [BatchController::class, 'show'])->middleware('perm:manufacturing.manage')->name('batches.show');
    Route::get('batches/{batch}/edit', [BatchController::class, 'edit'])->middleware('perm:manufacturing.manage')->name('batches.edit');
    Route::patch('batches/{batch}', [BatchController::class, 'update'])->middleware('perm:manufacturing.manage')->name('batches.update');
    Route::delete('batches/{batch}', [BatchController::class, 'destroy'])->middleware('perm:manufacturing.manage')->name('batches.destroy');

    Route::get('returns/customer', [CustomerReturnController::class, 'index'])->middleware('perm:sales.manage')->name('returns.customer.index');
    Route::get('returns/customer/create', [CustomerReturnController::class, 'create'])->middleware('perm:sales.manage')->name('returns.customer.create');
    Route::post('returns/customer', [CustomerReturnController::class, 'store'])->middleware('perm:sales.manage')->name('returns.customer.store');

    Route::get('returns/supplier', [SupplierReturnController::class, 'index'])->middleware('perm:control.suppliers')->name('returns.supplier.index');

    Route::get('campaigns', [CampaignController::class, 'index'])->middleware('perm:sales.manage')->name('campaigns.index');
    Route::get('campaigns/create', [CampaignController::class, 'create'])->middleware('perm:sales.manage')->name('campaigns.create');
    Route::post('campaigns', [CampaignController::class, 'store'])->middleware('perm:sales.manage')->name('campaigns.store');
    Route::get('campaigns/{campaign}/edit', [CampaignController::class, 'edit'])->middleware('perm:sales.manage')->name('campaigns.edit');
    Route::patch('campaigns/{campaign}', [CampaignController::class, 'update'])->middleware('perm:sales.manage')->name('campaigns.update');
    Route::delete('campaigns/{campaign}', [CampaignController::class, 'destroy'])->middleware('perm:sales.manage')->name('campaigns.destroy');

    Route::get('gifts', [CustomerGiftController::class, 'index'])->middleware('perm:sales.manage')->name('gifts.index');
    Route::get('gifts/create', [CustomerGiftController::class, 'create'])->middleware('perm:sales.manage')->name('gifts.create');
    Route::post('gifts', [CustomerGiftController::class, 'store'])->middleware('perm:sales.manage')->name('gifts.store');
    Route::get('gifts/{gift}/edit', [CustomerGiftController::class, 'edit'])->middleware('perm:sales.manage')->name('gifts.edit');
    Route::patch('gifts/{gift}', [CustomerGiftController::class, 'update'])->middleware('perm:sales.manage')->name('gifts.update');
    Route::delete('gifts/{gift}', [CustomerGiftController::class, 'destroy'])->middleware('perm:sales.manage')->name('gifts.destroy');
});

Route::middleware(['auth', 'client.portal'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [ClientPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('shipments', [ClientPortalController::class, 'shipments'])->name('shipments.index');
    Route::get('shipments/{shipment}', [ClientPortalController::class, 'showShipment'])->name('shipments.show');
    Route::get('orders', [ClientPortalController::class, 'orders'])->name('orders.index');
    Route::get('orders/{order}', [ClientPortalController::class, 'showOrder'])->name('orders.show');
    Route::get('invoices', [ClientPortalController::class, 'invoices'])->name('invoices.index');
    Route::get('invoices/{invoice}', [ClientPortalController::class, 'showInvoice'])->name('invoices.show');
    Route::get('deliveries', [ClientPortalController::class, 'deliveries'])->name('deliveries.index');
    Route::get('statement', [ClientPortalController::class, 'statement'])->name('statement');
    Route::get('profile', [ClientPortalController::class, 'profile'])->name('profile');
});
