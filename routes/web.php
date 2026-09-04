<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SubCustomerController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\RenewalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AdministrationController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\BillingCycleController;
use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ContractTypeController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\CommissionSlabController;
use App\Http\Controllers\PaymentChannelController;


Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/activities', [DashboardController::class, 'activities'])->name('dashboard.activities');
    Route::get('/global-search', [DashboardController::class, 'globalSearch'])->name('global-search');
    Route::get('/users', [AdministrationController::class, 'index'])->name('users.index');
    Route::get('/email-templates', [EmailTemplateController::class, 'index'])->name('email-templates.index');
    Route::get('/email-templates/create', [EmailTemplateController::class, 'create'])->name('email-templates.create');
    Route::post('/email-templates', [EmailTemplateController::class, 'store'])->name('email-templates.store');
    Route::get('/email-templates/{template}/edit', [EmailTemplateController::class, 'edit'])->name('email-templates.edit');
    Route::put('/email-templates/{template}', [EmailTemplateController::class, 'update'])->name('email-templates.update');
    Route::delete('/email-templates/{template}', [EmailTemplateController::class, 'destroy'])->name('email-templates.destroy');
    Route::resource('customers', CustomerController::class);
    Route::post('/customers/{customer}/inventory', [CustomerController::class, 'storeInventory'])->name('customers.storeInventory');
    Route::post('/customers/{customer}/invoices', [CustomerController::class, 'storeInvoice'])->name('customers.storeInvoice');
    Route::get('/customers/{customer}/sub-customers/create', [SubCustomerController::class, 'create'])->name('sub-customers.create');
    Route::post('/customers/{customer}/sub-customers', [SubCustomerController::class, 'store'])->name('sub-customers.store');
    Route::get('/sub-customers/{subCustomer}', [SubCustomerController::class, 'show'])->name('sub-customers.show');
    Route::get('/sub-customers/{subCustomer}/edit', [SubCustomerController::class, 'edit'])->name('sub-customers.edit');
    Route::put('/sub-customers/{subCustomer}', [SubCustomerController::class, 'update'])->name('sub-customers.update');
    Route::delete('/sub-customers/{subCustomer}', [SubCustomerController::class, 'destroy'])->name('sub-customers.destroy');
    Route::resource('subscriptions', SubscriptionController::class);
    Route::resource('payments', PaymentController::class);
    Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
    Route::get('/payments/{payment}/pdf', [PaymentController::class, 'downloadPdf'])->name('payments.pdf');
    Route::resource('invoices', InvoiceController::class);
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'printInvoice'])->name('invoices.print');
    Route::resource('inventory', InventoryController::class);
    Route::get('/expenses/{expense}/download', [ExpenseController::class, 'downloadReceipt'])->name('expenses.download');
    Route::resource('expenses', ExpenseController::class);
    Route::resource('purchases', PurchaseController::class);
    Route::resource('renewals', RenewalController::class);
    Route::post('/renewals/{renewalId}/renew', [RenewalController::class, 'renew'])->name('renewals.renew');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index')->middleware('permission:reports.view');
    Route::get('/reports/detail', [ReportController::class, 'detail'])->name('reports.detail')->middleware('permission:reports.view');
    Route::get('/exchange-rate', [PaymentController::class, 'exchangeRate'])->name('payments.exchange-rate');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index')->middleware('permission:settings.manage');
    Route::resource('billing-cycles', BillingCycleController::class)->middleware('permission:settings.manage');
    Route::post('/billing-cycles/{billing_cycle}/toggle', [BillingCycleController::class, 'toggleStatus'])->name('billing-cycles.toggle')->middleware('permission:settings.manage');
    Route::post('/import-excel', [ExcelImportController::class, 'import'])->name('import.excel');
    
    // Contracts & Contract Types
    Route::resource('contracts', ContractController::class);
    Route::post('/contracts/{contract}/duplicate', [ContractController::class, 'duplicate'])->name('contracts.duplicate');
    Route::get('/contracts/{contract}/attachment', [ContractController::class, 'attachment'])->name('contracts.attachment');
    Route::resource('contract-types', ContractTypeController::class);
    Route::post('/contract-types/{contract_type}/toggle', [ContractTypeController::class, 'toggleStatus'])->name('contract-types.toggle');
    
    // Payments Channel
    Route::resource('payment-channels', PaymentChannelController::class);
    Route::post('/payment-channels/{payment_channel}/toggle', [PaymentChannelController::class, 'toggleStatus'])->name('payment-channels.toggle');
    
    // User Resource (excluding index as it conflicts with the new /users route)
    Route::resource('users', \App\Http\Controllers\UserController::class)->except(['index'])->middleware('permission:users.manage');
    
    Route::get('/users/{user}/permissions', [\App\Http\Controllers\UserController::class, 'permissions'])->name('users.permissions');
    Route::post('/users/{user}/permissions', [\App\Http\Controllers\UserController::class, 'updatePermissions']);

    Route::get('/users/roles/create', [\App\Http\Controllers\RoleController::class, 'create'])->name('roles.create');
    Route::post('/users/roles', [\App\Http\Controllers\RoleController::class, 'store'])->name('roles.store');
    Route::get('/users/roles/{role}/permissions', [\App\Http\Controllers\RoleController::class, 'permissions'])->name('roles.permissions');
    Route::post('/users/roles/{role}/permissions', [\App\Http\Controllers\RoleController::class, 'updatePermissions']);
    Route::delete('/users/roles/{role}', [\App\Http\Controllers\RoleController::class, 'destroy'])->name('roles.destroy');

    Route::post('/bulk-delete/count', [\App\Http\Controllers\BulkDeleteController::class, 'count'])->name('bulk-delete.count');
    Route::post('/bulk-delete/execute', [\App\Http\Controllers\BulkDeleteController::class, 'execute'])->name('bulk-delete.execute');

    // Commission Module Routes
    Route::resource('commission-slabs', CommissionSlabController::class)->middleware('permission:settings.manage');
    Route::post('/commission-slabs/{commission_slab}/toggle', [CommissionSlabController::class, 'toggleStatus'])->name('commission-slabs.toggle')->middleware('permission:settings.manage');

    Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions.index')->middleware('permission:payments.view');
    Route::post('/commissions/calculate', [CommissionController::class, 'calculate'])->name('commissions.calculate')->middleware('permission:payments.view');
    Route::post('/commissions', [CommissionController::class, 'store'])->name('commissions.store')->middleware('permission:payments.view');
    Route::put('/commissions/{commission}', [CommissionController::class, 'update'])->name('commissions.update')->middleware('permission:payments.view');
    Route::delete('/commissions/{commission}', [CommissionController::class, 'destroy'])->name('commissions.destroy')->middleware('permission:payments.view');
});

Route::get('/temp-migrate', function() {
    try {
        \Illuminate\Support\Facades\DB::statement('DROP TABLE IF EXISTS purchase_items');
        \Illuminate\Support\Facades\Artisan::call('migrate');
        return 'SUCCESS';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});


