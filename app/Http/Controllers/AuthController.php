<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

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

    public function logout()
    {
        Auth::logout();
        Session::flush();

        return redirect()->to(route('login'));
    }
}
