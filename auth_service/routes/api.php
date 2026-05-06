<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::post('/register',    [AuthController::class, 'register']);
Route::post('/verify-otp',  [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp',  [AuthController::class, 'resendOtp']);
Route::post('/login',       [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::post('/internal/update-status', function (Request $request) {
    $user = \App\Models\User::where('email', $request->email)->first();
    if ($user) {
        $user->update(['status' => $request->status]);
        return response()->json(['message' => 'Status updated']);
    }
    return response()->json(['message' => 'User not found'], 404);
});
