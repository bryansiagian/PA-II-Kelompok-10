<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function showVerifyOtp()
    {
        if (!session('pending_otp_email')) return redirect('/register');
        return view('auth.verify_otp');
    }

    public function register(Request $request)
    {
        try {
            $response = Http::timeout(5)->post('http://localhost:8001/api/register', [
                'name'                  => $request->name,
                'email'                 => $request->email,
                'password'              => $request->password,
                'password_confirmation' => $request->password_confirmation,
                'phone'                 => $request->phone,
                'address'               => $request->address,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'message' => 'Layanan autentikasi sedang tidak tersedia. Silakan coba beberapa saat lagi.'
            ], 503);
        }

        $data = $response->json();

        if ($response->successful()) {
            $existingUser = User::where('email', $request->email)->first();
            if (!$existingUser) {
                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => bcrypt($request->password),
                    'phone'    => $request->phone,
                    'address'  => $request->address,
                    'status'   => 0,
                ]);
                $user->assignRole('customer');
            }

            session(['pending_otp_email' => $request->email]);

            return response()->json([
                'message'  => $data['message'],
                'redirect' => route('otp.verify'),
            ], 201);
        }

        return response()->json($data, $response->status());
    }

    public function verifyOtp(Request $request)
    {
        $email = session('pending_otp_email');
        if (!$email) {
            return response()->json(['message' => 'Sesi habis, silakan login kembali.'], 422);
        }

        try {
            $response = Http::timeout(5)->post('http://localhost:8001/api/verify-otp', [
                'email'    => $email,
                'otp_code' => $request->otp,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'message' => 'Layanan autentikasi sedang tidak tersedia. Silakan coba beberapa saat lagi.'
            ], 503);
        }

        $data = $response->json();

        if ($response->successful()) {
            session()->forget('pending_otp_email');
            return response()->json([
                'message'  => $data['message'],
                'redirect' => route('login'),
            ]);
        }

        return response()->json($data, $response->status());
    }

    public function resendOtp(Request $request)
    {
        $email = session('pending_otp_email');

        try {
            $response = Http::timeout(5)->post('http://localhost:8001/api/resend-otp', [
                'email' => $email,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'message' => 'Layanan autentikasi sedang tidak tersedia. Silakan coba beberapa saat lagi.'
            ], 503);
        }

        return response()->json($response->json(), $response->status());
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        try {
            $response = Http::timeout(5)->post('http://localhost:8001/api/login', [
                'email'    => $request->email,
                'password' => $request->password,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'message' => 'Layanan autentikasi sedang tidak tersedia. Silakan coba beberapa saat lagi.'
            ], 503);
        }

        $data = $response->json();

        // Belum verifikasi OTP
        if ($response->status() === 403) {
            session(['pending_otp_email' => $request->email]);
            return response()->json([
                'status'   => 'unverified',
                'message'  => $data['message'],
                'redirect' => route('otp.verify'),
            ], 403);
        }

        if (!$response->successful()) {
            return response()->json($data, $response->status());
        }

        // Login berhasil — buat session di app utama
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $user = User::create([
                'name'              => $data['user']['name'],
                'email'             => $data['user']['email'],
                'password'          => bcrypt($request->password),
                'phone'             => $data['user']['phone'] ?? null,
                'address'           => $data['user']['address'] ?? null,
                'status'            => $data['user']['status'],
                'email_verified_at' => now(),
            ]);
            $user->assignRole('customer');
        }

        // Sinkronisasi status dari auth-service
        $user->update([
            'status'            => $data['user']['status'],
            'email_verified_at' => $data['user']['email_verified_at'],
        ]);

        Auth::login($user, remember: true); // ← remember me agar session awet
        $request->session()->regenerate();

        session(['jwt_token' => $data['token']]);

        $user->tokens()->delete();
        $sanctumToken = $user->createToken('auth_token')->plainTextToken;
        session(['api_token' => $sanctumToken]);

        return response()->json([
            'status'   => 'success',
            'redirect' => '/dashboard',
        ]);
    }

    public function logout(Request $request)
    {
        // Invalidate JWT di auth-service (tidak masalah kalau auth-service mati)
        try {
            if (session('jwt_token')) {
                Http::timeout(3)->withToken(session('jwt_token'))
                    ->post('http://localhost:8001/api/logout');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Auth-service mati, lanjut logout lokal saja
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
