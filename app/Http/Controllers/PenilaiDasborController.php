<?php

namespace App\Http\Controllers;

class PenilaiDasborController extends Controller
{
    public function index()
    {
        $siswaList = auth()->user()->siswaBimbingan()->with('sekolah', 'penilaian')->orderBy('name', 'asc')->get();
        return view('penilai.index', compact('siswaList'));
    }
}
