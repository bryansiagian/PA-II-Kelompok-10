<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\OtpNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }

    public function showRegister() { return view('auth.register'); }

    public function showVerifyOtp() {
        if (!session('pending_otp_email')) return redirect('/register');
        return view('auth.verify_otp');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        return DB::transaction(function() use ($request) {

            $otp = rand(100000, 999999);

            $existingUser = User::where('email', $request->email)->first();

            if ($existingUser) {

                if ($existingUser->status != 2) {
                    return response()->json([
                        'message' => 'Email sudah terdaftar.'
                    ], 422);
                }

                // Reset user jika sebelumnya ditolak
                $user = $existingUser;
                $user->update([
                    'name' => $request->name,
                    'password' => Hash::make($request->password),
                    'address' => $request->address,
                    'status' => 0,
                    'otp_code' => $otp,
                    'otp_expires_at' => now()->addMinutes(10),
                    'email_verified_at' => null
                ]);

            } else {

                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'address' => $request->address,
                    'status' => 0,
                    'otp_code' => $otp,
                    'otp_expires_at' => now()->addMinutes(10),
                ]);

                $user->assignRole('customer');
            }

            // OPTIONAL EMAIL (boleh gagal)
            try {
                Mail::to($user->email)->send(new OtpNotification($user->name, $otp));
            } catch (\Exception $e) {
                \Log::error("Mail Error: " . $e->getMessage());
            }

            session(['pending_otp_email' => $user->email]);

            // 🔥 INI YANG PENTING (OTP DITAMPILKAN)
            return response()->json([
    'message' => 'OTP dikirim',
    'otp' => $otp, // 🔥 INI YANG DITAMBAHKAN
    'redirect' => route('otp.verify')
], 201);
        });
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email tidak ditemukan'], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Password salah'], 422);
        }

        // BELUM VERIFIKASI OTP
        if (is_null($user->email_verified_at)) {

            $otp = rand(100000, 999999);

            $user->update([
                'otp_code' => $otp,
                'otp_expires_at' => now()->addMinutes(10)
            ]);

            session(['pending_otp_email' => $user->email]);

            return response()->json([
                'status' => 'unverified',
                'otp' => $otp, // 🔥 tampilkan lagi
                'redirect' => route('otp.verify')
            ], 403);
        }

        // BELUM APPROVE ADMIN
        if ($user->status == 0) {
            return response()->json([
                'status' => 'pending',
                'message' => 'Menunggu persetujuan admin'
            ], 401);
        }

        if ($user->status == 2) {
            return response()->json([
                'status' => 'rejected',
                'message' => 'Akun ditolak admin'
            ], 401);
        }

        // LOGIN BERHASIL
        if (Auth::attempt($request->only('email', 'password'))) {

            $request->session()->regenerate();

            $user->tokens()->delete();

            session([
                'api_token' => $user->createToken('auth_token')->plainTextToken
            ]);

            return response()->json([
                'status' => 'success',
                'redirect' => '/dashboard'
            ]);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6'
        ]);

        $email = session('pending_otp_email');

        if (!$email) {
            return response()->json([
                'message' => 'Session habis'
            ], 422);
        }

        $user = User::where('email', $email)->first();

        if (!$user || $user->otp_code != $request->otp) {
            return response()->json([
                'message' => 'OTP salah'
            ], 422);
        }

        if (now()->isAfter($user->otp_expires_at)) {
            return response()->json([
                'message' => 'OTP expired'
            ], 422);
        }

        $user->update([
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null
        ]);

        session()->forget('pending_otp_email');

        return response()->json([
            'message' => 'Verifikasi berhasil',
            'redirect' => route('login')
        ]);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::user()->tokens()->delete();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
