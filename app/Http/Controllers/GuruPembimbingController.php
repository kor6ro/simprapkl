<?php

namespace App\Http\Controllers;

use App\Models\User;

class GuruPembimbingController extends Controller
{
    public function index()
    {
        $guru = auth()->user();
        $siswaList = collect();
        if ($guru->sekolah_id) {
            $siswaList = User::where('group_id', 4)->where('sekolah_id', $guru->sekolah_id)->with('penilaian')->orderBy('name', 'asc')->get();
        }
        return view('guru.index', compact('siswaList'));
    }
}
