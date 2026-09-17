<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FieldReportController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Public & Authentication
Route::get('/', fn() => redirect()->route('home'));
Route::get('/home', [AuthController::class, 'showLogin'])->name('home');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/user/login', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // User Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.changePassword');

    // Fields Management
    Route::get('/fields', [FieldReportController::class, 'index'])->name('fields.index');
    Route::get('/field/create', [FieldReportController::class, 'create'])->name('fields.create');
    Route::get('/fields/{field}', [FieldReportController::class, 'show'])->name('fields.show');
    Route::get('/fields/{field}/edit', [FieldReportController::class, 'edit'])->name('fields.edit');
    Route::post('/fields/{id?}', [FieldReportController::class, 'storeOrUpdateField'])->name('fields.save');
    Route::post('/fields/{field}/delete', [FieldReportController::class, 'destroyField'])->name('fields.delete');

    // Field Report Submissions & Views
    Route::prefix('fields/{field}')->group(function () {
        Route::get('/quality-checklist', [FieldReportController::class, 'createQualityChecklist'])->name('reports.quality.create');
        Route::post('/quality-checklist', [FieldReportController::class, 'storeQualityChecklist'])->name('reports.quality.store');

        Route::get('/submit-photo', [FieldReportController::class, 'createPhoto'])->name('reports.photo.create');
        Route::post('/submit-photo', [FieldReportController::class, 'storePhoto'])->name('reports.photo.store');

        Route::get('/submit-fertilization', [FieldReportController::class, 'createFertilization'])->name('reports.fert.create');
        Route::post('/submit-fertilization', [FieldReportController::class, 'storeFertilization'])->name('reports.fert.store');

        Route::get('/submit-color', [FieldReportController::class, 'createColor'])->name('reports.color.create');
        Route::post('/submit-color', [FieldReportController::class, 'storeColor'])->name('reports.color.store');

        Route::get('/submit-topdressing', [FieldReportController::class, 'createTopdressing'])->name('reports.topdressing.create');
        Route::post('/submit-topdressing', [FieldReportController::class, 'storeTopdressing'])->name('reports.topdressing.store');

        Route::get('/submit-overseeding', [FieldReportController::class, 'createOverseeding'])->name('reports.overseeding.create');
        Route::post('/submit-overseeding', [FieldReportController::class, 'storeOverseeding'])->name('reports.overseeding.store');

        Route::get('/submit-cultivation', [FieldReportController::class, 'createCultivation'])->name('reports.cultivation.create');
        Route::post('/submit-cultivation', [FieldReportController::class, 'storeCultivation'])->name('reports.cultivation.store');

        Route::get('/submit-pest', [FieldReportController::class, 'createPest'])->name('reports.pest.create');
        Route::post('/submit-pest', [FieldReportController::class, 'storePest'])->name('reports.pest.store');

        Route::get('/submit-thatch', [FieldReportController::class, 'createThatch'])->name('reports.thatch.create');
        Route::post('/submit-thatch', [FieldReportController::class, 'storeThatch'])->name('reports.thatch.store');

        Route::get('/submit-soil', [FieldReportController::class, 'createSoil'])->name('reports.soil.create');
        Route::post('/submit-soil', [FieldReportController::class, 'storeSoil'])->name('reports.soil.store');

        Route::get('/view-all-reports', [FieldReportController::class, 'viewAllReports'])->name('reports.viewAll');
    });

    // Report Actions
    Route::get('/report/{report}/view', [FieldReportController::class, 'viewReport'])->name('reports.view');
    Route::post('/report/{report}/delete', [FieldReportController::class, 'destroyReport'])->name('reports.delete');

    // Admin
    Route::middleware('can:access-admin')->group(function () {
        Route::get('/admin/submissions', [AdminController::class, 'submissions'])->name('admin.submissions');
    });
});
