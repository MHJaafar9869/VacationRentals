<?php

use App\Http\Controllers\AdminAuth\PasswordResetController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('/categories', CategoryController::class);

// >Property Route< //
Route::apiResource("property", PropertyController::class);
// ################ //

Route::get('/properties/search', [PropertyController::class, 'search']);

Route::get('/properties/category/{id}', [PropertyController::class, 'getpropertycategory']);

// ===================Auth Routes====================
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth:sanctum');

// ===================Owner Routes====================

Route::post('/register/owner', [App\Http\Controllers\AdminAuth\RegisteredUserController::class, 'store']);
Route::post('/login/owner', [App\Http\Controllers\AdminAuth\AuthenticatedSessionController::class, 'store']);
Route::post('/logout/owner', [App\Http\Controllers\AdminAuth\AuthenticatedSessionController::class, 'destroy'])->middleware('auth:sanctum');
Route::post('owners/password/email', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('owners/password/reset', [PasswordResetController::class, 'reset']);

// ===================End Owner Routes====================

// ===================Edit profile Routes====================
Route::put('/users/{id}', [UserController::class, 'updateprofile']);
Route::put('/owners/{id}', [OwnerController::class, 'updateprofile']);
