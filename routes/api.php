<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\DriverController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('agent')->group(function () {
        Route::get('products', [AgentController::class, 'products']);
        Route::get('orders', [AgentController::class, 'orders']);
        Route::get('orders/{order}', [AgentController::class, 'showOrder']);
        Route::post('orders', [AgentController::class, 'storeOrder']);
        Route::get('deliveries', [AgentController::class, 'deliveries']);
        Route::get('statement', [AgentController::class, 'statement']);
        Route::get('invoices', [AgentController::class, 'invoices']);
        Route::get('invoices/{invoice}', [AgentController::class, 'showInvoice']);
        Route::get('dashboard', [AgentController::class, 'dashboard']);
    });

    Route::prefix('employee')->group(function () {
        Route::get('profile', [EmployeeController::class, 'profile']);
        Route::get('contracts', [EmployeeController::class, 'contracts']);
        Route::get('allowances', [EmployeeController::class, 'allowances']);
        Route::post('allowances', [EmployeeController::class, 'storeAllowance']);
        Route::get('leaves', [EmployeeController::class, 'leaves']);
        Route::post('leaves', [EmployeeController::class, 'storeLeave']);
        Route::get('location-logs', [EmployeeController::class, 'locationLogs']);
        Route::post('location-logs', [EmployeeController::class, 'storeLocation']);
        Route::get('dashboard', [EmployeeController::class, 'dashboard']);
        Route::post('attendance/punch-in', [EmployeeController::class, 'punchIn']);
        Route::post('attendance/punch-out', [EmployeeController::class, 'punchOut']);
        Route::get('agents', [EmployeeController::class, 'agents']);
        Route::get('visit-plans', [EmployeeController::class, 'visitPlans']);
        Route::post('visit-plans', [EmployeeController::class, 'storeVisitPlan']);
        Route::post('visit-plans/{plan}/complete', [EmployeeController::class, 'completeVisitPlan']);
        Route::get('targets', [EmployeeController::class, 'targets']);
        Route::get('leaves/summary', [EmployeeController::class, 'leaveSummary']);
    });

    Route::prefix('driver')->group(function () {
        Route::get('deliveries', [DriverController::class, 'deliveries']);
        Route::get('deliveries/{delivery}', [DriverController::class, 'show']);
        Route::post('deliveries/{delivery}/status', [DriverController::class, 'updateStatus']);
        Route::post('deliveries/{delivery}/pod', [DriverController::class, 'uploadPod']);
        Route::get('vehicle-load', [DriverController::class, 'vehicleLoad']);
    });
});
