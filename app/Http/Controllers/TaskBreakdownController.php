<?php

namespace App\Http\Controllers;

use App\Models\TaskBreakdown;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TaskBreakdownController extends Controller
{
   public function store(Request $request)
{
     if (!in_array(auth()->user()->group_id, [2, 5])) {
        abort(403, 'Anda tidak memiliki wewenang untuk mengupload task breakdown.');
    }
    // [PERBAIKAN] Validasi disatukan menjadi satu blok yang lebih pintar
    $validated = $request->validate([
        'applicable_date' => 'required|date',
        'tipe' => 'required|in:file,teks',
        'deskripsi_tugas' => 'required_if:tipe,teks|nullable|string',
        'task_file' => 'required_if:tipe,file|nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
    ], [
        'deskripsi_tugas.required_if' => 'Deskripsi tugas wajib diisi jika Anda memilih tab Input Teks.',
        'task_file.required_if' => 'File wajib diunggah jika Anda memilih tab Upload File.',
    ]);

    $taskData = [
        'applicable_date' => $validated['applicable_date'],
        'tipe' => $validated['tipe'],
    ];

    // Cek dan hapus file lama jika ada, terutama saat menimpa
    $existingTask = TaskBreakdown::where('applicable_date', $taskData['applicable_date'])->first();
    if ($existingTask && $existingTask->task_breakdown) {
        File::delete(public_path('uploads/daily_tasks/' . $existingTask->task_breakdown));
    }

    if ($validated['tipe'] == 'teks') {
        $taskData['deskripsi_tugas'] = $validated['deskripsi_tugas'];
        $taskData['task_breakdown'] = null; // Pastikan kolom file kosong
    
    } else { // tipe == 'file'
        $file = $validated['task_file'];
        $fileName = $validated['applicable_date'] . '_' . time() . '.' . $file->getClientOriginalExtension();
        $folderTujuan = 'uploads/daily_tasks';
        $file->move(public_path($folderTujuan), $fileName);
        
        $taskData['task_breakdown'] = $fileName;
        $taskData['deskripsi_tugas'] = null; // Pastikan kolom teks kosong
    }

    TaskBreakdown::updateOrCreate(
        ['applicable_date' => $taskData['applicable_date']],
        $taskData
    );

    return redirect()->back()->with('success', 'Task breakdown harian berhasil disimpan!');
}
}