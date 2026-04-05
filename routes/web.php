<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\PollResponseController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PollController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
	Route::get('/inscription', [RegisteredUserController::class, 'create'])->name('register');
	Route::post('/inscription', [RegisteredUserController::class, 'store'])->name('register.store');
	Route::get('/connexion', [AuthenticatedSessionController::class, 'create'])->name('login');
	Route::post('/connexion', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
	Route::get('/dashboard', [PollController::class, 'dashboard'])->name('dashboard');
	Route::get('/dashboard/export/pdf', [PollController::class, 'exportDashboardPdf'])->name('dashboard.export.pdf');
	Route::get('/dashboard/export/csv', [PollController::class, 'exportDashboardCsv'])->name('dashboard.export.csv');
	Route::get('/dashboard/sondages/{poll}/modifier', [PollController::class, 'edit'])->name('polls.edit');
	Route::post('/sondages', [PollController::class, 'store'])->name('polls.store');
	Route::patch('/dashboard/sondages/{poll}', [PollController::class, 'update'])->name('polls.update');
	Route::delete('/dashboard/sondages/{poll}', [PollController::class, 'destroy'])->name('polls.destroy');
	Route::post('/deconnexion', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::get('/sondages/{poll}', [PollController::class, 'show'])->name('polls.show');
Route::post('/sondages/{poll}/reponses', [PollResponseController::class, 'store'])->name('polls.responses.store');
