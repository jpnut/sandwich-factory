<?php

use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SandwichOrderController;
use App\Http\Controllers\IngredientSubstitutionController;
use Illuminate\Support\Facades\Route;

Route::get('/', StoreController::class)->name('home');

Route::get('/sandwich-order', [SandwichOrderController::class, 'show'])->name('sandwich.order');
Route::post('/sandwich-order', [SandwichOrderController::class, 'store'])->name('sandwich.order.store');

Route::get('/settings', [SettingsController::class, 'show'])->name('settings');
Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
Route::post('/settings/reset-ingredients', [SettingsController::class, 'resetIngredients'])->name('settings.reset-ingredients');

Route::post('/ingredient-substitution', [IngredientSubstitutionController::class, 'store'])->name('ingredient.substitution.store');