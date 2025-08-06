<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;


class AuthController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect()->to(route('dashboard'));
        }

        return view('auth.index');
    }

    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect(route('login'))
                ->withErrors($validator)
                ->withInput();
        }

        $isAuth = Auth::attempt([
            'username' => $request->input('username'),
            'password' => $request->input('password')
        ]);

        if (!$isAuth) {
            return redirect(route('login'))
                ->withErrors(['auth_failed' => true]);
        }

        return redirect()->to(route('dashboard'));
    }
    public function showForgotPasswordForm()
    {
        return view('auth.forgotpass');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:user,email']);

        $token = Str::random(60);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );

        $url = route('password_reset', ['token' => $token]);

        Mail::raw("Klik link berikut untuk reset password: $url", function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Reset Password');
        });


        return back()->with('status', 'Link reset password telah dikirim keemail.');
    }

    public function showResetForm($token)
    {
        return view('auth.resetpass', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:user,email',
            'password' => 'required|confirmed|min:8',
            'token' => 'required'
        ]);

        $reset = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$reset) {
            return back()->withErrors(['email' => 'Token tidak valid atau sudah kadaluarsa.']);
        }

        User::where('email', $request->email)->update([
            'password' => bcrypt($request->password)
        ]);

        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect('/login')->with('status', 'Password berhasil direset. Silakan login.');
    }


    public function logout()
    {
        Auth::logout();
        Session::flush();

        return redirect()->to(route('login'));
    }
}
