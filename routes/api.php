<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AdminAuth\PasswordResetController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GmailController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\StripePaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('/categories', CategoryController::class);

// >Property Route< //
Route::apiResource("property", PropertyController::class);
// ################ //
// >Route For Stripe< //
Route::post('/payment', [StripePaymentController::class, 'createPaymentIntent']);
Route::post('/owner/{id}/register/stripe-account', [StripePaymentController::class, 'ownerCreateAccount']);
Route::post('/create-checkout-session', [StripePaymentController::class, 'createCheckoutSession']);
// ================== //
Route::get('/properties/search', [PropertyController::class, 'search']);

Route::get('/properties/category/{id}', [PropertyController::class, 'getpropertycategory']);

// ===================Auth Routes====================
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth:sanctum');
Route::post('/password/email', [PasswordResetController::class, 'sendResetLinkEmailUser']);
Route::post('/password/reset', [PasswordResetController::class, 'resetUser']);

// ========================Login With Google ================================

Route::controller(GmailController::class)->group(function () {
    Route::get('/gmail/login', 'login')->name('gmail.login');
    Route::get('/owner/gmail/login', ('loginOwner'))->name('owner.gmail.login');
    Route::get('/gmail/redirect', 'redirect')->name('gmail.redirect');
});

// ===================End Auth Routes====================
// ===================Owner Routes====================

Route::post('/register/owner', [App\Http\Controllers\AdminAuth\RegisteredUserController::class, 'store']);
Route::post('/login/owner', [App\Http\Controllers\AdminAuth\AuthenticatedSessionController::class, 'store']);
Route::post('/logout/owner', [App\Http\Controllers\AdminAuth\AuthenticatedSessionController::class, 'destroy'])->middleware('auth:sanctum');
Route::post('owners/password/email', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('owners/password/reset', [PasswordResetController::class, 'reset']);

// ===================End Owner Routes====================

// ===================Edit profile Routes====================
Route::put('/users/{id}', [UserController::class, 'updateProfile']);
Route::put('/owners/{id}', [OwnerController::class, 'updateProfile']);
// ===================location Routes====================
// Route::post('/search-location', [LocationController::class, 'searchLocation']);

// ===================Admin Routes====================
Route::controller(AdminController::class)
    ->prefix('admin')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/users', 'users');
        Route::get('/owners', 'owners');
        Route::get('/properties', 'properties');
    });

// ===================End Admin Routes====================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/favorites', [FavoriteController::class, 'addToFavorites']);
    Route::delete('/favorites', [FavoriteController::class, 'removeFromFavorites']);
    Route::get('/favorites', [FavoriteController::class, 'getUserFavorites']);
});