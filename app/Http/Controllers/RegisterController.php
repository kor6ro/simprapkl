<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function index()
    {
        $sekolahList = Sekolah::all();
        return view('auth.register', compact('sekolahList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:user',
            'password'   => 'required|min:6',
            'sekolah_id' => 'required|exists:sekolah,id',
        ]);

        User::create([
            'username'   => $request->username,
            'name'       => $request->name,
            'alamat'     => $request->alamat,            
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'validasi'   => $request->validasi ?? 0,
            'role'       => 'siswa',
            'group_id'   => $request->group_id ?? 1,    
            'sekolah_id' => $request->sekolah_id,
        ]);

        return redirect('/login')->with('success', 'Registrasi berhasil. Silakan login.');
    }
}
