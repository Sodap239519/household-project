<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', function(){ return redirect()->route('households.create'); });

Route::get('/households/create', [HouseholdController::class, 'create'])->name('households.create');
Route::post('/households', [HouseholdController::class, 'store'])->name('households.store');
Route::get('/households/{household}', [HouseholdController::class, 'show'])->name('households.show');


// Admin household list & export (ต้องใส่ middleware auth ในโปรดักชัน)
Route::get('/admin/households', [\App\Http\Controllers\Admin\HouseholdAdminController::class, 'index'])->name('admin.households.index');
Route::get('/admin/households/export', [\App\Http\Controllers\Admin\HouseholdAdminController::class, 'exportCsv'])->name('admin.households.export');

// Dashboard (public)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

// API endpoints for dashboard charts (public)
Route::get('/api/dashboard/area', [DashboardController::class, 'byArea']);
Route::get('/api/dashboard/gender', [DashboardController::class, 'byGender']);
Route::get('/api/dashboard/age', [DashboardController::class, 'byAgeRange']);
Route::get('/api/dashboard/finances', [DashboardController::class, 'financesByProvince']);
Route::get('/api/dashboard/status', [DashboardController::class, 'statusSummary']);