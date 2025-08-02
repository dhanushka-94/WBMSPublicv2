<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\WaterMeterController;
use App\Http\Controllers\MeterReadingController;
use App\Http\Controllers\GuarantorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SmsNotificationController;
use App\Http\Controllers\MonthlyActiveReportController;
use App\Http\Controllers\SystemControlController;

// Redirect root to login for admin access
Route::get('/', function () {
    return redirect()->route('login');
});

// Public About page
Route::get('/about', function () {
    return view('about');
})->name('about');

// System Control Routes (for admin/super admin only)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/system/status', [SystemControlController::class, 'status'])->name('system.status');
    Route::post('/system/enable', [SystemControlController::class, 'enable'])->name('system.enable');
    Route::post('/system/disable', [SystemControlController::class, 'disable'])->name('system.disable');
    Route::post('/system/toggle', [SystemControlController::class, 'toggle'])->name('system.toggle');
    Route::get('/system/get-status', [SystemControlController::class, 'getStatus'])->name('system.get-status');
});

// System disabled page (accessible to all authenticated users)
Route::middleware(['auth'])->group(function () {
    Route::get('/system/disabled', [SystemControlController::class, 'disabled'])->name('system.disabled');
});

// ALWAYS ACCESSIBLE ROUTES (viewing and payments only)
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard (always accessible)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile (always accessible)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // View-only customer routes (always accessible)
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('customers/{customer}/meters', [CustomerController::class, 'meters'])->name('customers.meters');
    Route::get('customers/{customer}/bills', [CustomerController::class, 'bills'])->name('customers.bills');
    
    // View-only bill routes (always accessible) 
    Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
    Route::get('/bills/{bill}', [BillController::class, 'show'])->name('bills.show');
    Route::get('bills/{bill}/print', [BillController::class, 'print'])->name('bills.print');
    Route::get('bills/data/ajax', [BillController::class, 'getBillsData'])->name('bills.data');
    
    // Payment routes (always accessible)
    Route::post('bills/{bill}/payment', [BillController::class, 'recordPayment'])->name('bills.payment');
});

// SYSTEM-CONTROLLED ROUTES (disabled when system is off)
Route::middleware(['auth', 'verified', 'system.check'])->group(function () {
    
    // User Management (admin only)
    Route::resource('users', UserController::class);
    
    // Settings routes (system management)
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
    
    // Customer Management (create/edit/delete - RESTRICTED)
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    
    // Guarantor Management  
    Route::resource('guarantors', GuarantorController::class);
    
    // Water Meter Management (RESTRICTED)
    Route::resource('water-meters', WaterMeterController::class)->names('meters');
    Route::get('meters/map-view', [WaterMeterController::class, 'mapView'])->name('meters.map');
    Route::post('meters/{meter}/maintenance', [WaterMeterController::class, 'recordMaintenance'])->name('meters.maintenance');
    Route::get('meters/{meter}/qr-code', [WaterMeterController::class, 'showQRCode'])->name('meters.qr-code');
    Route::get('meters/{meter}/qr-code/download', [WaterMeterController::class, 'downloadQRCode'])->name('meters.qr-code.download');
    Route::get('api/check-meter-number', [WaterMeterController::class, 'checkMeterNumber'])->name('check.meter.number');
    
    // Bill Management (create/edit/delete - RESTRICTED)
    Route::get('/bills/create', [BillController::class, 'create'])->name('bills.create');
    Route::post('/bills', [BillController::class, 'store'])->name('bills.store');
    Route::get('/bills/{bill}/edit', [BillController::class, 'edit'])->name('bills.edit');
    Route::put('/bills/{bill}', [BillController::class, 'update'])->name('bills.update');
    Route::delete('/bills/{bill}', [BillController::class, 'destroy'])->name('bills.destroy');
    Route::post('bills/generate', [BillController::class, 'generate'])->name('bills.generate');
    Route::post('bills/{bill}/send', [BillController::class, 'send'])->name('bills.send');
    Route::post('bills/calculate-late-fees', [BillController::class, 'calculateLateFees'])->name('bills.late-fees');
    
    // Meter Reading Management (RESTRICTED)
    Route::resource('meter-readings', MeterReadingController::class)->names('readings');
    Route::get('meter-readings/edit', [MeterReadingController::class, 'editFallback'])->name('readings.edit.fallback');
    Route::post('readings/{reading}/verify', [MeterReadingController::class, 'verify'])->name('readings.verify');
    Route::get('readings/bulk-entry', [MeterReadingController::class, 'bulkEntry'])->name('readings.bulk');
    Route::post('readings/bulk-store', [MeterReadingController::class, 'bulkStore'])->name('readings.bulk.store');
    Route::get('readings/monthly-schedule', [MeterReadingController::class, 'monthlySchedule'])->name('readings.schedule');
    Route::get('readings/data/ajax', [MeterReadingController::class, 'getReadingsData'])->name('readings.data');
    Route::get('readings/meter-details/ajax', [MeterReadingController::class, 'getMeterDetails'])->name('readings.meter-details');
    Route::get('readings/search-meters/ajax', [MeterReadingController::class, 'searchMeters'])->name('readings.search-meters');
    
    // SMS Notifications (RESTRICTED)
    Route::resource('sms-notifications', SmsNotificationController::class)->names('sms');
    Route::get('sms/statistics', [SmsNotificationController::class, 'statistics'])->name('sms.statistics');
    Route::post('sms/export', [SmsNotificationController::class, 'export'])->name('sms.export');
    Route::post('sms/bulk-delete', [SmsNotificationController::class, 'bulkDelete'])->name('sms.bulk-delete');
    Route::post('sms/{sms_notification}/resend', [SmsNotificationController::class, 'resend'])->name('sms.resend');
    Route::get('sms/templates', [SmsNotificationController::class, 'templates'])->name('sms.templates');
    Route::post('sms/due-reminders', [SmsNotificationController::class, 'sendDueReminders'])->name('sms.due-reminders');
    Route::post('sms/overdue-alerts', [SmsNotificationController::class, 'sendOverdueAlerts'])->name('sms.overdue-alerts');
    
    // Reports (RESTRICTED)
    Route::get('reports/consumption', [DashboardController::class, 'consumptionReport'])->name('reports.consumption');
    Route::get('reports/revenue', [DashboardController::class, 'revenueReport'])->name('reports.revenue');
    Route::get('reports/overdue', [DashboardController::class, 'overdueReport'])->name('reports.overdue');
    Route::get('reports/monthly-active', [MonthlyActiveReportController::class, 'index'])->name('reports.monthly-active');
    Route::get('reports/monthly-active/export', [MonthlyActiveReportController::class, 'export'])->name('reports.monthly-active.export');
    Route::get('reports/monthly-active/customers', [MonthlyActiveReportController::class, 'getMonthlyActiveCustomers'])->name('reports.monthly-active.customers');
    Route::get('reports/monthly-active/customers/download', [MonthlyActiveReportController::class, 'downloadMonthlyActiveCustomers'])->name('reports.monthly-active.customers.download');
    
    // Activity Logs (RESTRICTED)
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('activity-logs/{activity}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
    Route::get('activity-logs/user/{user}', [ActivityLogController::class, 'userActivity'])->name('activity-logs.user');
});

require __DIR__.'/auth.php';
