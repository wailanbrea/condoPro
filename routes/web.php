<?php

use App\Http\Controllers\CondominiumFundController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\Admin\ApartmentController;
use App\Http\Controllers\Admin\BankAccountController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\CondominiumController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ExtraChargeController;
use App\Http\Controllers\Admin\FinancialReportController;
use App\Http\Controllers\Admin\GasController;
use App\Http\Controllers\Admin\GasDeliveryController;
use App\Http\Controllers\Admin\GasInventoryController;
use App\Http\Controllers\Admin\GasTankSettingController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ApiController;
use App\Http\Controllers\Admin\ExpenseCategoryController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['es', 'en'])) {
        session(['locale' => $locale]);
        App::setLocale($locale);
    }
    return redirect()->back();
})->name('lang.switch');

Route::middleware(['auth', 'verified', 'role:super_admin,admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/condominium-fund', [CondominiumFundController::class, 'history'])->name('condominium-fund.history');
    Route::post('/condominium-fund/withdraw', [CondominiumFundController::class, 'withdraw'])->name('condominium-fund.withdraw');

    Route::resource('condominiums', CondominiumController::class);
    Route::resource('apartments', ApartmentController::class);
    Route::resource('users', UserController::class);
    Route::resource('billing', BillingController::class);
    Route::get('/billing/{billing}/pdf', [BillingController::class, 'downloadPdf'])->name('billing.pdf');
    Route::resource('gas', GasController::class);
    Route::get('/gas/inventario', [GasInventoryController::class, 'index'])->name('admin.gas.inventory');
    Route::get('/gas-tank', [GasTankSettingController::class, 'edit'])->name('gas-tank.edit');
    Route::put('/gas-tank', [GasTankSettingController::class, 'update'])->name('gas-tank.update');
    Route::post('/gas-tank/reset', [GasTankSettingController::class, 'reset'])->name('gas-tank.reset');
    Route::get('/gas-deliveries', [GasDeliveryController::class, 'index'])->name('gas-deliveries.index');
    Route::get('/gas-deliveries/{gasDelivery}', [GasDeliveryController::class, 'show'])->name('gas-deliveries.show');
    Route::resource('extra-charges', ExtraChargeController::class);
    Route::resource('payments', PaymentController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::resource('expense-categories', ExpenseCategoryController::class);
    Route::resource('bank-accounts', BankAccountController::class);
    Route::resource('announcements', AnnouncementController::class);
    Route::resource('reports', ReportController::class)->only(['index']);
    Route::resource('financial-reports', FinancialReportController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::post('/financial-reports/generate', [FinancialReportController::class, 'generate'])->name('financial-reports.generate');
    Route::post('/financial-reports/{financialReport}/close', [FinancialReportController::class, 'close'])->name('financial-reports.close');
    Route::post('/financial-movements', [FinancialReportController::class, 'storeMovement'])->name('financial-movements.store');
    Route::delete('/financial-movements/{movement}', [FinancialReportController::class, 'destroyMovement'])->name('financial-movements.destroy');

    Route::get('reports/apartment-statement/{apartment}', [ReportController::class, 'apartmentStatement'])->name('reports.apartment-statement');
    Route::get('reports/monthly-income', [ReportController::class, 'monthlyIncome'])->name('reports.monthly-income');
    Route::get('reports/monthly-expenses', [ReportController::class, 'monthlyExpenses'])->name('reports.monthly-expenses');
    Route::get('reports/monthly-balance', [ReportController::class, 'monthlyBalance'])->name('reports.monthly-balance');
    Route::get('reports/debtors', [ReportController::class, 'debtors'])->name('reports.debtors');
    Route::get('reports/pending-payments', [ReportController::class, 'pendingPayments'])->name('reports.pending-payments');
    Route::get('reports/gas-consumption', [ReportController::class, 'gasConsumption'])->name('reports.gas-consumption');
    Route::get('reports/extra-charges', [ReportController::class, 'extraCharges'])->name('reports.extra-charges');
    Route::get('reports/bills-status', [ReportController::class, 'billsStatus'])->name('reports.bills-status');

    Route::post('/payments/{payment}/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');
    Route::post('/payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
    Route::post('/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.markRead');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications/clear/all', [NotificationController::class, 'clearAll'])->name('notifications.clearAll');

    Route::get('/api/apartments-by-condominium/{condominium}', [ApiController::class, 'apartmentsByCondominium'])->name('api.apartments-by-condominium');
    Route::get('/api/gas-apartments-by-condominium/{condominium}', [ApiController::class, 'gasApartmentsByCondominium'])->name('api.gas-apartments-by-condominium');
});

Route::middleware(['auth', 'verified', 'role:resident,super_admin'])->prefix('resident')->group(function () {
    Route::get('/', [ResidentController::class, 'index'])->name('resident.index');
    Route::get('/condominium-fund', [CondominiumFundController::class, 'history'])->name('resident.condominium-fund');
    Route::get('/vouchers/upload', [ResidentController::class, 'uploadVoucher'])->name('resident.vouchers.upload');
    Route::post('/vouchers', [ResidentController::class, 'storeVoucher'])->name('resident.vouchers.store');
    Route::get('/vouchers/success', [ResidentController::class, 'voucherSuccess'])->name('resident.vouchers.success');
    Route::get('/invoices', [ResidentController::class, 'invoices'])->name('resident.invoices');
    Route::get('/invoices/{bill}', [ResidentController::class, 'invoiceDetail'])->name('resident.invoices.show');
    Route::get('/history', [ResidentController::class, 'history'])->name('resident.history');
    Route::get('/announcements', [ResidentController::class, 'announcements'])->name('resident.announcements');
    Route::get('/notifications', [ResidentController::class, 'notifications'])->name('resident.notifications');
    Route::post('/notifications/{notification}/mark-read', [ResidentController::class, 'markNotificationRead'])->name('resident.notifications.markRead');
    Route::post('/notifications/mark-all-read', [ResidentController::class, 'markAllNotificationsRead'])->name('resident.notifications.markAllRead');
    Route::delete('/notifications/{notification}', [ResidentController::class, 'destroyNotification'])->name('resident.notifications.destroy');
    Route::delete('/notifications/clear/all', [ResidentController::class, 'clearAllNotifications'])->name('resident.notifications.clearAll');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';