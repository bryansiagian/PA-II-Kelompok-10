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

Route::post('/forgot-password', function (Request $request) {
    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'Email tidak ditemukan.'], 404);
    }

    if ($user->status == 2) {
        return response()->json(['message' => 'Akun Anda telah ditolak oleh admin.'], 403);
    }

    if ($user->status == 0) {
        return response()->json(['message' => 'Akun Anda belum disetujui admin.'], 403);
    }

    $otp = rand(100000, 999999);
    $user->update([
        'otp_code'       => $otp,
        'otp_expires_at' => \Carbon\Carbon::now()->addMinutes(10),
    ]);

    try {
        \Illuminate\Support\Facades\Mail::to($user->email)->send(
            new \App\Mail\ForgotPasswordOtp($user->name, $otp)
        );
    } catch (\Exception $e) {
        \Log::error('Gagal kirim OTP forgot password: ' . $e->getMessage());
        return response()->json(['message' => 'Gagal mengirim OTP. Coba lagi nanti.'], 500);
    }

    return response()->json(['message' => 'OTP telah dikirim ke email Anda.']);
});

Route::post('/reset-password', function (Request $request) {
    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'Email tidak ditemukan.'], 404);
    }

    if ($user->otp_code !== $request->otp_code) {
        return response()->json(['message' => 'Kode OTP salah.'], 422);
    }

    if (\Carbon\Carbon::now()->isAfter($user->otp_expires_at)) {
        return response()->json(['message' => 'OTP sudah kedaluwarsa.'], 422);
    }

    $user->update([
        'password'       => \Illuminate\Support\Facades\Hash::make($request->password),
        'otp_code'       => null,
        'otp_expires_at' => null,
    ]);

    return response()->json(['message' => 'Password berhasil direset.']);
});
