<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\GuarantorController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\WaterMeterController;
use App\Http\Controllers\MeterReadingController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivityLogController;

// Redirect root to login for admin access
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::resource('users', UserController::class);
    
    // Settings routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        
        // Division routes
        Route::post('/divisions', [SettingsController::class, 'storeDivision'])->name('divisions.store');
        Route::put('/divisions/{division}', [SettingsController::class, 'updateDivision'])->name('divisions.update');
        Route::delete('/divisions/{division}', [SettingsController::class, 'destroyDivision'])->name('divisions.destroy');
        
        // Customer Type routes
        Route::post('/customer-types', [SettingsController::class, 'storeCustomerType'])->name('customer-types.store');
        Route::put('/customer-types/{customerType}', [SettingsController::class, 'updateCustomerType'])->name('customer-types.update');
        Route::delete('/customer-types/{customerType}', [SettingsController::class, 'destroyCustomerType'])->name('customer-types.destroy');
        
        // Billing Settings routes
        Route::get('/billing', [SettingsController::class, 'billingSettings'])->name('billing.index');
        Route::post('/billing/bulk-update', [SettingsController::class, 'bulkUpdateBillingDates'])->name('billing.bulk-update');
        Route::put('/billing/customer/{customer}', [SettingsController::class, 'updateCustomerBilling'])->name('billing.update-customer');
        Route::post('/billing/calculate-dates', [SettingsController::class, 'calculateBillingDates'])->name('billing.calculate-dates');
        
        // System Billing Configuration routes
        Route::get('/system-billing', [SettingsController::class, 'systemBillingConfig'])->name('system-billing');
        Route::put('/system-billing', [SettingsController::class, 'updateSystemBillingConfig'])->name('system-billing.update');
        Route::post('/system-billing/apply-all', [SettingsController::class, 'applyDefaultBillingToAll'])->name('system-billing.apply-all');
        
        // Rate Management routes
        Route::resource('rates', RateController::class);
        Route::post('rates/{rate}/toggle-status', [RateController::class, 'toggleStatus'])->name('rates.toggle-status');
        Route::get('rates/{rate}/duplicate', [RateController::class, 'duplicate'])->name('rates.duplicate');
    });
    
    // Customer Management
    Route::resource('customers', CustomerController::class);
    Route::get('customers/{customer}/meters', [CustomerController::class, 'meters'])->name('customers.meters');
    Route::get('customers/{customer}/bills', [CustomerController::class, 'bills'])->name('customers.bills');
    
    // Guarantor Management
    Route::resource('guarantors', GuarantorController::class);
    
    // Water Meter Management
    Route::resource('water-meters', WaterMeterController::class)->names('meters');
    Route::get('meters/map-view', [WaterMeterController::class, 'mapView'])->name('meters.map');
    Route::post('meters/{meter}/maintenance', [WaterMeterController::class, 'recordMaintenance'])->name('meters.maintenance');
    
    // Bill Management
    Route::resource('bills', BillController::class);
    Route::post('bills/generate', [BillController::class, 'generate'])->name('bills.generate');
    Route::post('bills/{bill}/send', [BillController::class, 'send'])->name('bills.send');
    Route::post('bills/{bill}/payment', [BillController::class, 'recordPayment'])->name('bills.payment');
    Route::get('bills/{bill}/print', [BillController::class, 'print'])->name('bills.print');
    Route::get('bills/data/ajax', [BillController::class, 'getBillsData'])->name('bills.data');
    Route::post('bills/calculate-late-fees', [BillController::class, 'calculateLateFees'])->name('bills.late-fees');
    
    // Meter Reading Management
    Route::resource('meter-readings', MeterReadingController::class)->names('readings');
    Route::post('readings/{reading}/verify', [MeterReadingController::class, 'verify'])->name('readings.verify');
    Route::get('readings/bulk-entry', [MeterReadingController::class, 'bulkEntry'])->name('readings.bulk');
    Route::post('readings/bulk-store', [MeterReadingController::class, 'bulkStore'])->name('readings.bulk.store');
    Route::get('readings/monthly-schedule', [MeterReadingController::class, 'monthlySchedule'])->name('readings.schedule');
    Route::get('readings/data/ajax', [MeterReadingController::class, 'getReadingsData'])->name('readings.data');
    Route::get('readings/meter-details/ajax', [MeterReadingController::class, 'getMeterDetails'])->name('readings.meter-details');
    Route::get('readings/search-meters/ajax', [MeterReadingController::class, 'searchMeters'])->name('readings.search-meters');
    
    // Reports
    Route::get('reports/consumption', [DashboardController::class, 'consumptionReport'])->name('reports.consumption');
    Route::get('reports/revenue', [DashboardController::class, 'revenueReport'])->name('reports.revenue');
    Route::get('reports/overdue', [DashboardController::class, 'overdueReport'])->name('reports.overdue');
    
    // Activity Logs
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('activity-logs/{activity}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
    Route::get('activity-logs/user/{user}', [ActivityLogController::class, 'userActivity'])->name('activity-logs.user');
    Route::get('activity-logs/export/data', [ActivityLogController::class, 'export'])->name('activity-logs.export');
    Route::post('activity-logs/cleanup', [ActivityLogController::class, 'cleanup'])->name('activity-logs.cleanup');
    Route::get('activity-logs/stats/ajax', [ActivityLogController::class, 'stats'])->name('activity-logs.stats');
    Route::get('activity-logs/recent/ajax', [ActivityLogController::class, 'recent'])->name('activity-logs.recent');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
