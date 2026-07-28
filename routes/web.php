<?php

use App\Http\Controllers\Admin\InvoiceAdminController;
use App\Http\Controllers\Admin\ProfileChangeRequestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceTemplateController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StaffMemberController;
use App\Http\Controllers\StaffPortal\AuthController as StaffPortalAuthController;
use App\Http\Controllers\StaffPortal\DashboardController as StaffPortalDashboardController;
use App\Http\Controllers\SubcontractorOnboardingController;
use App\Http\Controllers\SystemSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.store');
});

Route::get('/subcontractor-onboarding', [SubcontractorOnboardingController::class, 'create'])->name('subcontractor-onboardings.create');
Route::post('/subcontractor-onboarding', [SubcontractorOnboardingController::class, 'store'])->name('subcontractor-onboardings.store');
Route::redirect('/employee-registration', '/subcontractor-onboarding')->name('employee-registrations.create');
Route::get('/invoice-template/download', [InvoiceTemplateController::class, 'download'])->name('invoice-template.download');

Route::get('/staff-portal', [StaffPortalAuthController::class, 'login'])->name('staff-portal.login');
Route::post('/staff-portal/request-code', [StaffPortalAuthController::class, 'requestCode'])->name('staff-portal.request-code');
Route::get('/staff-portal/verify', [StaffPortalAuthController::class, 'verify'])->name('staff-portal.verify');
Route::post('/staff-portal/verify', [StaffPortalAuthController::class, 'checkCode'])->name('staff-portal.check-code');
Route::post('/staff-portal/logout', [StaffPortalAuthController::class, 'logout'])->name('staff-portal.logout');
Route::get('/staff-portal/dashboard', [StaffPortalDashboardController::class, 'index'])->name('staff-portal.dashboard');
Route::get('/staff-portal/invoices', [StaffPortalDashboardController::class, 'invoices'])->name('staff-portal.invoices');
Route::get('/staff-portal/profile', [StaffPortalDashboardController::class, 'profile'])->name('staff-portal.profile');
Route::get('/staff-portal/invoices/template', [StaffPortalDashboardController::class, 'downloadInvoiceTemplate'])->name('staff-portal.invoices.template');
Route::post('/staff-portal/invoices', [StaffPortalDashboardController::class, 'uploadInvoice'])->name('staff-portal.invoices.store');
Route::get('/staff-portal/invoices/{invoice}/download', [StaffPortalDashboardController::class, 'downloadInvoice'])->name('staff-portal.invoices.download');
Route::get('/staff-portal/invoices/{invoice}/remittance', [StaffPortalDashboardController::class, 'downloadRemittance'])->name('staff-portal.invoices.remittance');
Route::post('/staff-portal/profile-update', [StaffPortalDashboardController::class, 'requestProfileUpdate'])->name('staff-portal.profile-update');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::post('/api/bookings', [BookingController::class, 'storeFromWebsite'])
    ->middleware('throttle:60,1')
    ->name('api.bookings.store');

Route::middleware(['auth'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/media/{path}', [MediaController::class, 'show'])->where('path', '.*')->name('media.show');
    Route::get('staff-members/{staffMember}/documents/{field}', [StaffMemberController::class, 'document'])->name('staff-members.documents.show');
    Route::get('staff-members/{staffMember}/documents/{field}/download', [StaffMemberController::class, 'downloadDocument'])->name('staff-members.documents.download');
    Route::delete('staff-members/{staffMember}/documents/{field}', [StaffMemberController::class, 'deleteDocument'])->name('staff-members.documents.destroy');
    Route::resource('staff-members', StaffMemberController::class)->only(['index', 'show', 'edit', 'update', 'destroy']);
    Route::get('staff-invoices', [InvoiceAdminController::class, 'index'])->name('staff-invoices.index');
    Route::get('staff-invoices/{invoice}/review', [InvoiceAdminController::class, 'showReview'])->name('staff-invoices.review');
    Route::get('staff-invoices/{invoice}/download', [InvoiceAdminController::class, 'download'])->name('staff-invoices.download');
    Route::get('staff-invoices/{invoice}/remittance', [InvoiceAdminController::class, 'downloadRemittance'])->name('staff-invoices.remittance');
    Route::delete('staff-invoices/{invoice}', [InvoiceAdminController::class, 'destroy'])->name('staff-invoices.destroy');
    Route::post('staff-invoices/{invoice}/correction', [InvoiceAdminController::class, 'requestCorrection'])->name('staff-invoices.correction');
    Route::post('staff-invoices/{invoice}/ready', [InvoiceAdminController::class, 'markReady'])->name('staff-invoices.ready');
    Route::post('staff-invoices/{invoice}/paid', [InvoiceAdminController::class, 'markPaid'])->name('staff-invoices.paid');
    Route::post('staff-invoices/{invoice}/remittance/send', [InvoiceAdminController::class, 'sendRemittance'])->name('staff-invoices.remittance.send');
    Route::post('staff-invoices/work-logs/{workLog}/approve', [InvoiceAdminController::class, 'approveWorkLog'])->name('staff-invoices.work-logs.approve');
    Route::get('staff-profile-changes', [ProfileChangeRequestController::class, 'index'])->name('staff-profile-changes.index');
    Route::get('staff-profile-changes/{profileChange}/documents/{field}', [ProfileChangeRequestController::class, 'document'])->name('staff-profile-changes.documents.show');
    Route::get('staff-profile-changes/{profileChange}/documents/{field}/download', [ProfileChangeRequestController::class, 'downloadDocument'])->name('staff-profile-changes.documents.download');
    Route::post('staff-profile-changes/{profileChange}/approve', [ProfileChangeRequestController::class, 'approve'])->name('staff-profile-changes.approve');
    Route::post('staff-profile-changes/{profileChange}/reject', [ProfileChangeRequestController::class, 'reject'])->name('staff-profile-changes.reject');
    Route::get('subcontractor-onboardings', [SubcontractorOnboardingController::class, 'index'])->name('subcontractor-onboardings.index');
    Route::get('subcontractor-onboardings/{subcontractorOnboarding}/edit', [SubcontractorOnboardingController::class, 'edit'])->name('subcontractor-onboardings.edit');
    Route::put('subcontractor-onboardings/{subcontractorOnboarding}', [SubcontractorOnboardingController::class, 'update'])->name('subcontractor-onboardings.update');
    Route::get('subcontractor-onboardings/{subcontractorOnboarding}', [SubcontractorOnboardingController::class, 'show'])->name('subcontractor-onboardings.show');
    Route::post('subcontractor-onboardings/{subcontractorOnboarding}/approve', [SubcontractorOnboardingController::class, 'approve'])->name('subcontractor-onboardings.approve');
    Route::post('subcontractor-onboardings/{subcontractorOnboarding}/reject', [SubcontractorOnboardingController::class, 'reject'])->name('subcontractor-onboardings.reject');
    Route::get('subcontractor-onboardings/{subcontractorOnboarding}/documents/{field}', [SubcontractorOnboardingController::class, 'document'])->name('subcontractor-onboardings.documents.show');
    Route::get('subcontractor-onboardings/{subcontractorOnboarding}/documents/{field}/download', [SubcontractorOnboardingController::class, 'downloadDocument'])->name('subcontractor-onboardings.documents.download');
    Route::delete('subcontractor-onboardings/{subcontractorOnboarding}/documents/{field}', [SubcontractorOnboardingController::class, 'deleteDocument'])->name('subcontractor-onboardings.documents.destroy');
    Route::redirect('employee-registrations', 'subcontractor-onboardings')->name('employee-registrations.index');
    Route::redirect('employee-registrations/{employeeRegistration}', 'subcontractor-onboardings')->name('employee-registrations.show');
    Route::get('invoice-template', [InvoiceTemplateController::class, 'edit'])->name('invoice-template.edit');
    Route::post('invoice-template', [InvoiceTemplateController::class, 'update'])->name('invoice-template.update');
    Route::delete('invoice-template', [InvoiceTemplateController::class, 'destroy'])->name('invoice-template.destroy');
    Route::get('settings/business-information', [SystemSettingsController::class, 'editBusiness'])->name('settings.business');
    Route::post('settings/business-information', [SystemSettingsController::class, 'updateBusiness'])->name('settings.business.update');
    Route::get('settings/maintenance', [SystemSettingsController::class, 'edit'])->name('settings.maintenance');
    Route::post('settings/clear-cache', [SystemSettingsController::class, 'clearCache'])->name('settings.clear-cache');
    Route::post('settings/clear-temporary-data', [SystemSettingsController::class, 'clearTemporaryData'])->name('settings.clear-temporary-data');
});
