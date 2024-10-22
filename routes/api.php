<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AdminAuth\PasswordResetController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GmailController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use App\Http\Controllers\StripePaymentController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Pusher\Pusher;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/user/info', [UserController::class, 'getUserInfo'])->middleware('auth:sanctum');
Route::apiResource('/categories', CategoryController::class);

// >Property Related< //
Route::apiResource("property", PropertyController::class);
Route::post('property/{id}/amenities', [PropertyController::class, 'storeAmenities'])->middleware('auth:sanctum');
Route::post('property/{id}/images', [PropertyController::class, 'storeImages'])->middleware('auth:sanctum');
Route::put('property/{id}/update-images', [PropertyController::class, 'updateImages'])->middleware('auth:sanctum');
Route::put('property/{id}/update-amenities', [PropertyController::class, 'updateAmenities'])->middleware('auth:sanctum');
Route::get('/property-amenities/{id}', [PropertyController::class, 'getPropertyAmenities']);
Route::post('/properties/filter', [PropertyController::class, 'filter']);
Route::post('/properties/category', [PropertyController::class, 'filterByCategory']);
// ################ //

// >Route For Stripe< //
Route::post('/payment', [StripePaymentController::class, 'createPaymentIntent']);
Route::post('/owner/{id}/register/stripe-account', [StripePaymentController::class, 'ownerCreateAccount']);
Route::post('/create-checkout-session', [StripePaymentController::class, 'createCheckoutSession']);
Route::get('/amenities', [PropertyController::class, 'getAmenities']);
// ================== //
// >Booking related< //
Route::get('/properties/search', [PropertyController::class, 'search']);
Route::get('/location-suggestions', [PropertyController::class, 'getSuggestions']);

Route::post('/booking/message', [MessageController::class, 'message'])->middleware('auth:sanctum');
Route::get('rooms/{propertyId}/{userId}/{bookingId}', [MessageController::class, 'getRoomDetails'])->middleware('auth:sanctum');


Route::post('/pusher/auth', function (Request $request) {
    return Broadcast::auth($request);
})->middleware('auth:sanctum');

Route::get('/booking/owner-details/{id}', [BookingController::class, 'getOwnerInfo']);
// ================= //

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
Route::get('/users/{id}', [UserController::class, 'getUserProfile']);
Route::put('/users/{id}', [UserController::class, 'updateProfile']);
Route::get('/owners/{id}', [OwnerController::class, 'show']);
Route::put('/owners/{id}', [OwnerController::class, 'updateProfile']);

Route::put('/users/{id}', [UserController::class, 'updateProfile']);
Route::put('/owners/{id}', [OwnerController::class, 'updateProfile']);

Route::middleware('auth:sanctum')->get('/user/payments', [UserController::class, 'userWithPayments']);
Route::middleware('auth:sanctum')->get('/owner/details', [OwnerController::class, 'ownerDetails']);
Route::get('/users/{id}', [UserController::class, 'getUserById']);
// ===================Admin Routes====================
Route::controller(AdminController::class)
    ->prefix('admin')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/users', 'users');
        Route::get('/owners', 'owners');
        Route::delete('/deleteuser/{id}', 'deleteuser');
        Route::delete('/deleteowner/{id}', 'deleteowner');
        Route::patch('/properties/{id}/update-status', 'update');
        Route::post('/send-email/{id}', 'sendEmail');
        Route::get('/properties', 'index');
        Route::get('/properties/{id}', 'show');
        Route::get('/showowner/{id}', 'showowner');
        Route::get('/payments', 'payments');
    });
// ===================End Admin Routes====================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/favorites', [FavoriteController::class, 'addToFavorites']);
    Route::delete('/favorites/{property_id}', [FavoriteController::class, 'removeFromFavorites']);
    Route::get('/favorites', [FavoriteController::class, 'getUserFavorites']);
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggleFavorite']);
    Route::get('bookings/{id}', [BookingController::class, 'userData']);
});

Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)->name('verification.verify')->middleware('auth:sanctum');
Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('auth:sanctum')->name('verification.send');

Route::post('/properties/{id}/accept', [AdminController::class, 'acceptProperty']);
Route::post('/properties/{id}/reject', [AdminController::class, 'rejectProperty']);
Route::controller(StripePaymentController::class)->group(function () {
    Route::post('stripe', 'stripe')->name('stripe')->middleware('auth:sanctum');
    Route::get('success', 'success')->name('success');
    Route::get('cancel', 'cancel')->name('cancel');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reviews', [ReviewController::class, 'addReview']);
});

Route::get('/properties/{id}/reviews', [ReviewController::class, 'getPropertyReviews']);
Route::delete('/reviews/{id}', [ReviewController::class, 'deleteReview']);

Route::middleware('auth:sanctum')->get('/owner', [OwnerController::class, 'getOwnerDetails']);
Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'getUserDetails']);

Route::get('/admin/owner/{id}', [AdminController::class, 'getOwnerDetails']);
Route::delete('/properties/{id}', [PropertyController::class, 'delete']);

Route::get('/test-pusher', function () {
    $options = [
        'cluster' => env('PUSHER_APP_CLUSTER'),
        'useTLS' => true,
    ];

    $pusher = new Pusher(
        env('PUSHER_APP_KEY'),
        env('PUSHER_APP_SECRET'),
        env('PUSHER_APP_ID'),
        $options
    );

    $data['message'] = 'Test message';
    $pusher->trigger('test-channel', 'test-event', $data);

    return 'Message sent!';
})->middleware('auth:sanctum');
