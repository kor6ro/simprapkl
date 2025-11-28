<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
// --- TAMBAHKAN USE STATEMENT INI ---
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
// --- SELESAI ---
use Illuminate\Validation\Rules\Password as PasswordRules;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login.
     * Jika pengguna sudah login, alihkan ke dashboard.
     */
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.index');
    }

    /**
     * Memproses permintaan autentikasi (login).
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // --- KODE BARU DIMULAI ---

        // 1. Buat 'throttle key' yang unik berdasarkan username dan alamat IP.
        $throttleKey = Str::lower($request->input('username')) . '|' . $request->ip();

        // 2. Cek apakah sudah terlalu banyak percobaan
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) { // 5 kali percobaan
            $seconds = RateLimiter::availableIn($throttleKey);
            $errorMessage = 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . $seconds . ' detik.';
            
            // Kirim pesan error dan flag 'locked' ke view
            return back()->with('error', $errorMessage)
                         ->with('locked', true)
                         ->withInput(['username' => $request->username]);
        }

        // --- KODE BARU SELESAI ---

        // Mencoba untuk melakukan login
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Regenerasi session untuk mencegah serangan session fixation
            $request->session()->regenerate();

            // --- KODE BARU DIMULAI ---
            // 4. Jika berhasil, hapus hitungan percobaan
            RateLimiter::clear($throttleKey);
            // --- KODE BARU SELESAI ---

            // Arahkan ke halaman yang dituju sebelumnya atau ke dashboard
            return redirect()->intended(route('dashboard'));
        }

        // --- KODE BARU DIMULAI ---
        // 3. Jika gagal, tambahkan satu hitungan percobaan
        RateLimiter::hit($throttleKey, 30); // Dibekukan selama 30 detik
        // --- KODE BARU SELESAI ---

        // Jika autentikasi gagal, kembali ke halaman login dengan pesan error
        return back()->with('error', 'Username atau Password yang Anda masukkan salah.')
                     ->withInput(['username' => $request->username]); // Kirim kembali input username
    }

    // ... (sisa method lainnya tidak perlu diubah) ...
    public function showForgotPasswordForm()
    {
        return view('auth.forgotpass');
    }

    public function showResetForm(Request $request, $token)
    {
        return view('auth.resetpass', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => ['required', 'confirmed', PasswordRules::min(8)],
        ]);

        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $request->email)->first();

        if (!$tokenData || !Hash::check($request->token, $tokenData->token)) {
            return back()->withErrors(['email' => 'Token tidak valid atau sudah kadaluarsa.']);
        }
        
        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Password Anda berhasil direset. Silakan login kembali.');
    }
    
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}