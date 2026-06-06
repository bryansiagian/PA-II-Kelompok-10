<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    private function authServiceUrl(): string
    {
        return env('AUTH_SERVICE_URL', 'http://127.0.0.1:8001');
    }

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
        // Cegah register ulang dengan email yang sudah ada di sistem
        $existing = User::where('email', $request->email)->first();

        if ($existing) {
            if ($existing->status === 2) {
                return response()->json([
                    'message' => 'Email ini telah ditolak oleh admin. Silakan hubungi administrator untuk informasi lebih lanjut.',
                ], 422);
            }

            if ($existing->status === 1) {
                return response()->json([
                    'message' => 'Email ini sudah terdaftar. Silakan login.',
                ], 422);
            }

            if ($existing->status === 0) {
                return response()->json([
                    'message' => 'Email ini sudah terdaftar dan sedang menunggu persetujuan admin.',
                ], 422);
            }
        }

        try {
            $response = Http::timeout(30)->retry(3, 500, throw: false)->post(
                $this->authServiceUrl() . '/api/register',
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
                'message'  => $data['message'] ?? 'Registrasi berhasil. Silakan cek email Anda.',
                'redirect' => route('otp.verify'),
            ], 201);
        }

        return response()->json([
            'message' => $data['message'] ?? 'Registrasi gagal. Silakan coba lagi.',
        ], $response->status());
    }

    public function verifyOtp(Request $request)
    {
        $email = session('pending_otp_email') ?? $request->email;

        if (!$email) {
            return response()->json(['message' => 'Email tidak ditemukan.'], 422);
        }

        try {
            $response = Http::timeout(15)->retry(3, 500, throw: false)->post($this->authServiceUrl() . '/api/verify-otp', [
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

        return response()->json([
            'message' => $data['message'] ?? 'Kode OTP tidak valid atau sudah kedaluwarsa.',
        ], $response->status());
    }

    public function resendOtp(Request $request)
    {
        $email = session('pending_otp_email');

        try {
            $response = Http::timeout(10)->retry(3, 500, throw: false)->post($this->authServiceUrl() . '/api/resend-otp', [
                'email' => $email,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'message' => 'Layanan autentikasi sedang tidak tersedia. Silakan coba beberapa saat lagi.',
            ], 503);
        }

        return response()->json([
            'message' => $response->json('message') ?? 'Gagal mengirim ulang OTP.',
        ], $response->status());
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Cek status user di DB lokal lebih dulu sebelum forward ke auth-service
        $localUser = User::where('email', $request->email)->first();

        if ($localUser) {
            if ($localUser->status === 0) {
                return response()->json([
                    'message' => 'Akun Anda sedang menunggu persetujuan admin.',
                ], 401);
            }

            if ($localUser->status === 2) {
                return response()->json([
                    'message' => 'Akun Anda telah ditolak. Silakan periksa email Anda untuk informasi lebih lanjut.',
                ], 422);
            }
        }

        try {
            $response = Http::timeout(30)->retry(3, 500, throw: false)->post($this->authServiceUrl() . '/api/login', [
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
                'message'  => $data['message'] ?? 'Email belum diverifikasi.',
                'redirect' => route('otp.verify'),
            ], 403);
        }

        if (!$response->successful()) {
            return response()->json([
                'message' => $data['message'] ?? 'Email atau password salah.',
            ], $response->status());
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
                    ->post($this->authServiceUrl() . '/api/logout');
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
