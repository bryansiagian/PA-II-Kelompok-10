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
        return view('auth.verify_otp', [
            'email' => session('pending_otp_email'),
        ]);
    }

    public function register(Request $request)
    {
        try {
            $response = Http::timeout(30)->retry(3, 500)->post(
                'http://127.0.0.1:8001/api/register',
                [
                    'name'                  => $request->name,
                    'email'                 => $request->email,
                    'password'              => $request->password,
                    'password_confirmation' => $request->password_confirmation,
                    'phone'                 => $request->phone,
                    'address'               => $request->address,
                    'regency'               => $request->regency,
                    'district'              => $request->district,
                    'village'               => $request->village,
                ]
            );
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'message' => 'Layanan autentikasi sedang tidak tersedia. Silakan coba beberapa saat lagi.',
            ], 503);
        }

        $data = $response->json();

        if ($response->successful()) {
            session([
                'pending_otp_email' => $request->email,
                'pending_name'      => $request->name,
                'pending_phone'     => $request->phone,
                'pending_address'   => $request->address,
                'pending_regency'   => $request->regency,
                'pending_district'  => $request->district,
                'pending_village'   => $request->village,
                'pending_password'  => $request->password,
            ]);

            return response()->json([
                'message'  => $data['message'],
                'redirect' => route('otp.verify'),
            ], 201);
        }

        return response()->json($data, $response->status());
    }

    public function verifyOtp(Request $request)
    {
        $email = session('pending_otp_email') ?? $request->email;

        if (!$email) {
            return response()->json(['message' => 'Email tidak ditemukan.'], 422);
        }

        try {
            $response = Http::timeout(15)->retry(3, 500)->post('http://127.0.0.1:8001/api/verify-otp', [
                'email'    => $email,
                'otp_code' => $request->otp,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json(['message' => 'Layanan autentikasi sedang tidak tersedia.'], 503);
        }

        $data = $response->json();

        if ($response->successful()) {
            try {
                $user = User::where('email', $email)->first();

                if (!$user) {
                    $user = User::create([
                        'name'              => session('pending_name') ?? 'User',
                        'email'             => $email,
                        'password'          => bcrypt(session('pending_password')),
                        'phone'             => session('pending_phone'),
                        'address'           => session('pending_address'),
                        'regency'           => session('pending_regency'),
                        'district'          => session('pending_district'),
                        'village'           => session('pending_village'),
                        'status'            => 0,
                        'email_verified_at' => now(),
                    ]);
                    $user->assignRole('customer');
                } else {
                    $user->update([
                        'email_verified_at' => now(),
                        'status'            => 0,
                        'phone'             => session('pending_phone')    ?? $user->phone,
                        'address'           => session('pending_address')  ?? $user->address,
                        'regency'           => session('pending_regency')  ?? $user->regency,
                        'district'          => session('pending_district') ?? $user->district,
                        'village'           => session('pending_village')  ?? $user->village,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Gagal update user di db utama: ' . $e->getMessage());
            }

            session()->forget([
                'pending_otp_email',
                'pending_name',
                'pending_phone',
                'pending_address',
                'pending_regency',
                'pending_district',
                'pending_village',
                'pending_password',
            ]);

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
            $response = Http::timeout(10)->retry(3, 500)->post('http://127.0.0.1:8001/api/resend-otp', [
                'email' => $email,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'message' => 'Layanan autentikasi sedang tidak tersedia. Silakan coba beberapa saat lagi.',
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
            $response = Http::timeout(30)->retry(3, 500)->post('http://127.0.0.1:8001/api/login', [
                'email'    => $request->email,
                'password' => $request->password,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'message' => 'Layanan autentikasi sedang tidak tersedia. Silakan coba beberapa saat lagi.',
            ], 503);
        }

        $data = $response->json();

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

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $user = User::create([
                'name'              => $data['user']['name'],
                'email'             => $data['user']['email'],
                'password'          => bcrypt($request->password),
                'phone'             => $data['user']['phone']    ?? null,
                'address'           => $data['user']['address']  ?? null,
                'regency'           => $data['user']['regency']  ?? null,
                'district'          => $data['user']['district'] ?? null,
                'village'           => $data['user']['village']  ?? null,
                'status'            => $data['user']['status'],
                'email_verified_at' => now(),
            ]);
            $user->assignRole('customer');
        }

        $user->update([
            'status'            => $data['user']['status'],
            'email_verified_at' => $data['user']['email_verified_at'],
        ]);

        Auth::login($user, remember: true);
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
        try {
            if (session('jwt_token')) {
                Http::timeout(3)->withToken(session('jwt_token'))
                    ->post('http://127.0.0.1:8001/api/logout');
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
