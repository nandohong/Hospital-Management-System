<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistrationController;

Route::get('/', [RegistrationController::class, 'index'])
    ->name('registrations.index');

Route::post('/registrations', [RegistrationController::class, 'store'])
    ->name('registrations.store');