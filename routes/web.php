<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Http\Controllers\TodosPageController::class);

Route::get('/admin/login', [\App\Http\Controllers\AdminLoginController::class, 'show']);
Route::post('/admin/login', [\App\Http\Controllers\AdminLoginController::class, 'store']);
