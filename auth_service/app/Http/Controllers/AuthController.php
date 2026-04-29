<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\OtpNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email',
            'password' => 'required|confirmed|min:6',
            'phone'    => 'required|string|max:20',
            'address'  => 'required|string',
        ]);

        return DB::transaction(function () use ($request) {
            $otp = rand(100000, 999999);
            $existingUser = User::where('email', $request->email)->first();

            if ($existingUser) {
                // Jika akun aktif atau pending, tolak
                if ($existingUser->status != 2) {
                    return response()->json([
                        'message' => 'Email ini sudah terdaftar dan aktif/menunggu verifikasi.'
                    ], 422);
                }

                // Jika akun ditolak (status 2), reset dan daftar ulang
                $existingUser->update([
                    'name'           => $request->name,
                    'phone'          => $request->phone,
                    'password'       => Hash::make($request->password),
                    'address'        => $request->address,
                    'status'         => 0,
                    'otp_code'       => $otp,
                    'otp_expires_at' => Carbon::now()->addMinutes(10),
                    'email_verified_at' => null,
                ]);
                $user = $existingUser;
            } else {
                // Email baru
                $user = User::create([
                    'name'           => $request->name,
                    'email'          => $request->email,
                    'phone'          => $request->phone,
                    'password'       => Hash::make($request->password),
                    'address'        => $request->address,
                    'status'         => 0,
                    'active'         => 1,
                    'otp_code'       => $otp,
                    'otp_expires_at' => Carbon::now()->addMinutes(10),
                ]);

                // Kirim info ke app utama untuk assign role 'customer'
                // (akan kita setup nanti via HTTP call)
            }

            try {
                Mail::to($user->email)->send(new OtpNotification($user->name, $otp));
            } catch (\Exception $e) {
                \Log::error("Mail Error: " . $e->getMessage());
            }

            return response()->json([
                'message' => 'OTP dikirim ke email Anda',
                'email'   => $user->email,
            ], 201);
        });
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'otp_code' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->otp_code !== $request->otp_code) {
            return response()->json(['message' => 'Kode OTP salah.'], 422);
        }

        if (Carbon::now()->isAfter($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP sudah kadaluarsa.'], 422);
        }

        $user->email_verified_at = Carbon::now();
        $user->otp_code          = null;
        $user->otp_expires_at    = null;
        $user->save();

        return response()->json([
            'message' => 'Verifikasi email berhasil! Menunggu persetujuan admin.'
        ]);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        }

        $otp = rand(100000, 999999);
        $user->update([
            'otp_code'       => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        try {
            Mail::to($user->email)->send(new OtpNotification($user->name, $otp));
            return response()->json(['message' => 'Kode OTP baru telah dikirim ke email Anda.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengirim email. Coba lagi nanti.'], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Alamat email tidak ditemukan.'], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Kata sandi salah.'], 422);
        }

        // Belum verifikasi OTP
        if (is_null($user->email_verified_at)) {
            // Kirim ulang OTP jika expired
            if (!$user->otp_code || Carbon::now()->isAfter($user->otp_expires_at)) {
                $otp = rand(100000, 999999);
                $user->update([
                    'otp_code'       => $otp,
                    'otp_expires_at' => Carbon::now()->addMinutes(10),
                ]);
                try {
                    Mail::to($user->email)->send(new OtpNotification($user->name, $otp));
                } catch (\Exception $e) {
                    \Log::error($e->getMessage());
                }
            }

            return response()->json([
                'status'  => 'unverified',
                'message' => 'Email belum diverifikasi. Silakan masukkan kode OTP.',
                'email'   => $user->email,
            ], 403);
        }

        // Akun pending approval admin
        if ($user->status == 0) {
            return response()->json([
                'status'  => 'pending',
                'message' => 'Akun Anda sedang menunggu persetujuan admin.',
            ], 401);
        }

        // Akun ditolak
        if ($user->status == 2) {
            return response()->json([
                'status'  => 'rejected',
                'message' => 'Pendaftaran akun Anda telah ditolak oleh admin.',
            ], 401);
        }

        // Login & generate JWT
        if (!$token = JWTAuth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Kredensial tidak valid.'], 401);
        }

        return response()->json([
            'status' => 'success',
            'token'  => $token,
            'user'   => $user,
        ]);
    }

    public function logout()
    {
        try {
            JWTAuth::invalidateToken(JWTAuth::getToken());
            return response()->json(['message' => 'Logout berhasil.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal logout.'], 500);
        }
    }
}
