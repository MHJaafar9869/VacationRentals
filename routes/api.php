<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PropertyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('/categories', CategoryController::class);

// >Property Route< //
Route::apiResource("property", PropertyController::class);
// ################ //