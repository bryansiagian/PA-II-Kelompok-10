<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    private function authServiceUrl(): string
    {
        return env('AUTH_SERVICE_URL', 'http://127.0.0.1:8001');
    }

    public function showForgotForm()
    {
        return view('auth.forgot_password');
    }

    public function showResetForm()
    {
        return view('auth.reset_password', [
            'email' => session('forgot_email'),
        ]);
    }

    /**
     * Step 1: Kirim OTP ke email
     */
    public function sendOtp(Request $request)
    {
        try {
            $response = Http::timeout(15)->retry(3, 500, throw: false)
                ->post($this->authServiceUrl() . '/api/forgot-password', [
                    'email' => $request->email,
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json(['message' => 'Layanan autentikasi sedang tidak tersedia.'], 503);
        }

        $data = $response->json();

        if ($response->successful()) {
            session(['forgot_email' => $request->email]);
            return response()->json([
                'message'  => $data['message'] ?? 'OTP telah dikirim.',
                'redirect' => route('password.reset.form'),
            ]);
        }

        return response()->json([
            'message' => $data['message'] ?? 'Gagal mengirim OTP.',
        ], $response->status());
    }

    /**
     * Step 2: Verifikasi OTP + reset password di kedua DB
     */
    public function resetPassword(Request $request)
    {
        $email = session('forgot_email') ?? $request->email;

        if (!$email) {
            return response()->json(['message' => 'Sesi tidak valid. Ulangi dari awal.'], 422);
        }

        if ($request->password !== $request->password_confirmation) {
            return response()->json(['message' => 'Konfirmasi password tidak cocok.'], 422);
        }

        // Reset di auth_service (verifikasi OTP + update password)
        try {
            $response = Http::timeout(15)->retry(3, 500, throw: false)
                ->post($this->authServiceUrl() . '/api/reset-password', [
                    'email'    => $email,
                    'otp_code' => $request->otp_code,
                    'password' => $request->password,
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json(['message' => 'Layanan autentikasi sedang tidak tersedia.'], 503);
        }

        $data = $response->json();

        if (!$response->successful()) {
            return response()->json([
                'message' => $data['message'] ?? 'Gagal mereset password.',
            ], $response->status());
        }

        // Reset di DB utama juga
        try {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update(['password' => Hash::make($request->password)]);
            }
        } catch (\Exception $e) {
            \Log::error('Gagal update password di DB utama: ' . $e->getMessage());
        }

        session()->forget('forgot_email');

        return response()->json([
            'message'  => 'Password berhasil direset. Silakan login.',
            'redirect' => route('login'),
        ]);
    }
}
