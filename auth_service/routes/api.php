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
        $user->update([
            'status'            => $request->status,
            'email_verified_at' => $request->status == 1 ? now() : null, // ← tambah ini
        ]);
        return response()->json(['message' => 'Status updated']);
    }
    return response()->json(['message' => 'User not found'], 404);
});

Route::post('/internal/delete-user', function (Request $request) {
    $user = \App\Models\User::where('email', $request->email)->first();
    if ($user) {
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }
    return response()->json(['message' => 'User not found'], 404);
});

Route::post('/internal/recreate-user', function (Request $request) {
    $user = \App\Models\User::where('email', $request->email)->first();

    if ($user) {
        // Sudah ada, update saja
        $user->update([
            'name'              => $request->name,
            'password'          => \Illuminate\Support\Facades\Hash::make($request->password),
            'status'            => 1,
            'email_verified_at' => now(),
            'otp_code'          => null,
            'otp_expires_at'    => null,
        ]);
    } else {
        // Buat baru
        \App\Models\User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'phone'             => $request->phone  ?? '',
            'address'           => $request->address ?? '',
            'password'          => \Illuminate\Support\Facades\Hash::make($request->password),
            'status'            => 1,
            'active'            => 1,
            'email_verified_at' => now(),
        ]);
    }

    return response()->json(['message' => 'User recreated']);
});
