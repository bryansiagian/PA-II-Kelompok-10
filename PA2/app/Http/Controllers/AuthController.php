<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
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

    // Tampilkan halaman input OTP
    public function showVerifyOtp() {
        if (!session('pending_otp_email')) return redirect('/register');
        return view('auth.verify_otp');
    }

    public function register(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email', // Hapus unique dulu di sini, kita validasi manual di bawah
            'password' => 'required|confirmed|min:6',
        ]);

        return DB::transaction(function() use ($request) {
            $otp = rand(100000, 999999);

            // Cari apakah email sudah terdaftar
            $existingUser = User::where('email', $request->email)->first();

            if ($existingUser) {
                // Jika akun sudah aktif atau masih pending, tidak boleh daftar lagi
                if ($existingUser->status != 2) {
                    return response()->json(['message' => 'Email ini sudah terdaftar dan aktif/menunggu verifikasi.'], 422);
                }

                // Jika akun sebelumnya DITOLAK (Status 2), timpa datanya (Reset Akun)
                $user = $existingUser;
                $user->update([
                    'name' => $request->name,
                    'password' => Hash::make($request->password),
                    'address' => $request->address,
                    'status' => 0, // Kembalikan ke Pending
                    'otp_code' => $otp,
                    'otp_expires_at' => Carbon::now()->addMinutes(10),
                    'email_verified_at' => null // Wajib verifikasi ulang
                ]);
            } else {
                // Jika email benar-benar baru
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'address' => $request->address,
                    'status' => 0,
                    'otp_code' => $otp,
                    'otp_expires_at' => Carbon::now()->addMinutes(10),
                ]);
                $user->assignRole('customer');
            }

            try {
                Mail::to($user->email)->send(new OtpNotification($user->name, $otp));
            } catch (\Exception $e) {
                \Log::error("Mail Error: " . $e->getMessage());
            }

            session(['pending_otp_email' => $user->email]);

            return response()->json([
                'message' => 'OTP dikirim ke email Anda',
                'redirect' => route('otp.verify')
            ], 201);
        });
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 1. Ambil data user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // 2. Cek apakah email terdaftar
        if (!$user) {
            return response()->json(['message' => 'Alamat email tidak ditemukan.'], 404);
        }

        // 3. Cek apakah password benar
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Kata sandi salah.'], 422);
        }

        // 4. CEK VERIFIKASI EMAIL (OTP)
        // Jika kolom email_verified_at berisi NULL, berarti belum OTP.
        if (is_null($user->email_verified_at)) {
            // Cek apakah perlu kirim OTP baru atau pakai yang lama?
            if (!$user->otp_code || now()->isAfter($user->otp_expires_at)) {
                $otp = rand(100000, 999999);
                $user->update([
                    'otp_code' => $otp,
                    'otp_expires_at' => now()->addMinutes(10)
                ]);
                try {
                    Mail::to($user->email)->send(new \App\Mail\OtpNotification($user->name, $otp));
                } catch (\Exception $e) { \Log::error($e->getMessage()); }
            }

            session(['pending_otp_email' => $user->email]);
            return response()->json([
                'status' => 'unverified',
                'message' => 'Email belum diverifikasi. Silakan masukkan kode OTP.',
                'redirect' => route('otp.verify')
            ], 403);
        }

        // 5. CEK APPROVAL ADMIN (Hanya jika Email SUDAH Verified)
        if ($user->status == 0) { // Pending
            return response()->json([
                'status' => 'pending',
                'message' => 'Verifikasi email berhasil. Akun Anda saat ini menunggu persetujuan Admin pusat.'
            ], 401);
        }

        if ($user->status == 2) { // Rejected
            return response()->json([
                'status' => 'rejected',
                'message' => 'Maaf, pendaftaran akun Anda telah ditolak oleh Admin.'
            ], 401);
        }

        // 6. LOGIN RESMI (Lolos Semua Filter)
        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            $user->tokens()->delete();
            session(['api_token' => $user->createToken('auth_token')->plainTextToken]);

            return response()->json([
                'status' => 'success',
                'redirect' => '/dashboard'
            ]);
        }
    }

    public function verifyOtp(Request $request) {
        $request->validate(['otp' => 'required|digits:6']);

        $email = session('pending_otp_email');
        if (!$email) return response()->json(['message' => 'Sesi habis, silakan login kembali.'], 422);

        $user = User::where('email', $email)->first();

        if (!$user || $user->otp_code !== $request->otp) {
            return response()->json(['message' => 'Kode OTP salah.'], 422);
        }

        if (now()->isAfter($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP Kedaluwarsa.'], 422);
        }

        // UPDATE: Pastikan email_verified_at benar-benar terisi
        $user->email_verified_at = now(); // Gunakan assignment langsung agar lebih pasti
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save(); // Simpan ke database secara eksplisit

        session()->forget('pending_otp_email');

        return response()->json([
            'message' => 'Verifikasi email berhasil! Sekarang silakan menunggu persetujuan Admin.',
            'redirect' => route('login')
        ]);
    }

    public function logout(Request $request) {
        if (Auth::check()) {
            Auth::user()->tokens()->delete();
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
