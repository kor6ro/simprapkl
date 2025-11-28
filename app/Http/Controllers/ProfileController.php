<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman detail profil pengguna.
     */
    public function index()
    {
        $profile = User::with("sekolah", "group", "programKeahlian", "periodePkl")
            ->where("id", Auth::id())
            ->first();

        if (!$profile) {
            return abort(404);
        }

        return view("administrator.profile.index", ["profile" => $profile]);
    }

    /**
     * Menampilkan form untuk mengedit profil.
     */
    public function edit()
    {
        $profile = User::where("id", Auth::id())->first();
        if (!$profile) {
            return abort(404);
        }

        return view("administrator.profile.edit", ["profile" => $profile]);
    }

    /**
     * Menyimpan perubahan data profil ke database.
     */
    public function save(Request $request)
    {
        $profile = User::where("id", Auth::id())->first();
        if (!$profile) {
            return abort(404);
        }

        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "username" => "required|string|max:255",
            "email" => "required|email|max:255",
            "alamat" => "required|string",
            "photo_profile" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048",
        ]);

        if ($validator->fails()) {
            return redirect(route("profile.edit"))
                ->withErrors($validator)
                ->withInput();
        }

        $dataSave = [
            "name" => $request->input("name"),
            "username" => $request->input("username"),
            "email" => $request->input("email"),
            "alamat" => $request->input("alamat"),
        ];

        // Logika upload foto profil standar
        if ($request->hasFile('photo_profile')) {
            // Hapus foto lama jika ada
            if ($profile->photo_profile && File::exists(public_path('uploads/profiles/' . $profile->photo_profile))) {
                File::delete(public_path('uploads/profiles/' . $profile->photo_profile));
            }

            // Simpan file baru
            $file = $request->file('photo_profile');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/profiles'), $filename);
            $dataSave['photo_profile'] = $filename;
        }

        try {
            $profile->update($dataSave);
            return redirect(route("profile.index"))->with([
                "dataSaved" => true,
                "message" => "Data profil berhasil diupdate.",
            ]);
        } catch (\Throwable $th) {
            return redirect(route("profile.index"))->with([
                "dataSaved" => false,
                "message" => "Terjadi kesalahan saat mengupdate data: " . $th->getMessage(),
            ]);
        }
    }

    /**
     * Menampilkan form ganti password.
     */
    public function showChangePasswordForm()
    {
        return view('administrator.profile.change-password');
    }

    /**
     * Memvalidasi dan menyimpan password baru.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('Password saat ini tidak cocok.');
                }
            }],
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'new_password.min' => 'Password baru minimal harus 8 karakter.'
        ]);

        $user->forceFill([
            'password' => Hash::make($request->input('new_password')),
        ])->save();
        
        return redirect()->route('profile.index')->with('status', 'password-updated')->with([
            "dataSaved" => true,
            "message" => "Password berhasil diubah.",
        ]);

    }
}