<?php

namespace App\Http\Controllers;

use App\Helpers\PresensiHelper;
use App\Models\Presensi;
use App\Models\PresensiSetting;
use App\Models\PresensiStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class PresensiController extends Controller
{
    /**
     * Halaman utama presensi (1 halaman, multi-tab).
     */
    public function index()
    {
        $user    = Auth::user();
        $today   = now()->toDateString();
        $setting = PresensiSetting::first();

        $presensiHariIni = Presensi::where('user_id', $user->id)
            ->where('tanggal_presensi', $today)
            ->get();

        $statusPresensi = $this->getStatusPresensiHariIni($presensiHariIni, $setting);

        return view('administrator.presensi.index', compact('statusPresensi', 'setting'));
    }

    /**
     * DataTables: Presensi Hari Ini (detail per sesi)
     */
    public function dataHariIni()
    {
        $today = now()->toDateString();

        $data = Presensi::with(['user.sekolah', 'presensiStatus'])
            ->whereDate('tanggal_presensi', $today)
            ->orderBy('presensi.created_at', 'desc');

        // Jika user adalah siswa, filter hanya miliknya
        if (Auth::user()->group_id == 4) {
            $data->where('user_id', Auth::id());
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nama', fn($row) => $row->user->name ?? '-')
            ->addColumn('sekolah', fn($row) => $row->user->sekolah->nama ?? '-')
            ->addColumn('status_badge', fn($row) => $this->renderStatusBadge($row))
            ->addColumn('bukti_foto', function ($row) {
                if ($row->bukti_foto && $row->bukti_foto !== 'default.jpg') {
                    $url = asset('storage/' . $row->bukti_foto);
                    return '<a href="' . $url . '" target="_blank"><img src="' . $url . '" width="60" class="rounded"></a>';
                }
                return '-';
            })
            ->rawColumns(['status_badge', 'bukti_foto'])
            ->make(true);
    }

    /**
     * DataTables: Semua Presensi (SUMMARY per hari - gabungan pagi+sore)
     */
    /**
     * DataTables: Semua Presensi (SUMMARY per hari - gabungan pagi+sore)
     */
    public function dataSemua()
    {
        try {
            // Debug: Log request
            \Log::info('dataSemua called', [
                'user_id' => Auth::id(),
                'group_id' => Auth::user()->group_id
            ]);

            // Ambil raw data presensi terlebih dahulu
            $baseQuery = Presensi::with(['user.sekolah'])
                ->select([
                    'user_id',
                    'tanggal_presensi',
                    \DB::raw('MIN(created_at) as earliest_created_at'),
                    \DB::raw('MIN(id) as min_id') // Tambah ini untuk unique identifier
                ])
                ->groupBy('user_id', 'tanggal_presensi')
                ->orderBy('tanggal_presensi', 'desc');

            // Filter jika siswa
            if (Auth::user()->group_id == 4) {
                $baseQuery->where('user_id', Auth::id());
            }

            // Debug: Cek ada data atau tidak
            $count = $baseQuery->count();
            \Log::info('Data count:', ['count' => $count]);

            return DataTables::of($baseQuery)
                ->addIndexColumn()
                ->addColumn('nama', function ($row) {
                    return $row->user->name ?? 'Unknown';
                })
                ->addColumn('sekolah', function ($row) {
                    return $row->user->sekolah->nama ?? 'Unknown';
                })
                ->addColumn('tanggal', function ($row) {
                    return \Carbon\Carbon::parse($row->tanggal_presensi)->format('d/m/Y');
                })
                ->addColumn('status_badge', function ($row) {
                    try {
                        $statusHarian = \App\Helpers\presensihelper::hitungStatusHarian(
                            $row->user_id,
                            $row->tanggal_presensi
                        );
                        $color = \App\Helpers\presensihelper::getStatusColor($statusHarian);
                        $statusText = ucfirst($statusHarian); // This will display as "Alpa" in UI

                        $badge = '<span class="badge bg-' . $color . '">' . $statusText . '</span>';

                        // Keep this condition as lowercase
                        if ($statusHarian === 'alpa' && Auth::id() == $row->user_id) {
                            // Check if there's already a pending request
                            $pendingRequest = Presensi::where('user_id', $row->user_id)
                                ->where('tanggal_presensi', $row->tanggal_presensi)
                                ->where('approval_status', 'pending')
                                ->exists();

                            if (!$pendingRequest) {
                                $badge .= '<br><button class="btn btn-sm btn-outline-warning mt-1" 
                     onclick="requestApprovalForDate(\'' . $row->tanggal_presensi . '\')" 
                     title="Request perubahan status Alpa">
                     📝 Request Approval
                  </button>';
                            } else {
                                $badge .= '<br><small class="text-warning">⏳ Menunggu approval</small>';
                            }
                        }

                        return $badge;
                    } catch (\Exception $e) {
                        \Log::error('Error in status_badge:', ['error' => $e->getMessage()]);
                        return '<span class="badge bg-secondary">Error</span>';
                    }
                })

                ->addColumn('detail_sesi', function ($row) {
                    try {
                        // Ambil data pagi dan sore
                        $pagi = Presensi::where('user_id', $row->user_id)
                            ->where('tanggal_presensi', $row->tanggal_presensi)
                            ->where('sesi', 'pagi')
                            ->first();

                        $sore = Presensi::where('user_id', $row->user_id)
                            ->where('tanggal_presensi', $row->tanggal_presensi)
                            ->where('sesi', 'sore')
                            ->first();

                        $pagiStatus = $pagi ? ($pagi->status ?? 'Unknown') : 'Tidak Presensi';
                        $soreStatus = $sore ? ($sore->status ?? 'Unknown') : 'Tidak Presensi';

                        $pagiJam = $pagi && $pagi->jam_presensi
                            ? \Carbon\Carbon::parse($pagi->jam_presensi)->format('H:i')
                            : '-';
                        $soreJam = $sore && $sore->jam_presensi
                            ? \Carbon\Carbon::parse($sore->jam_presensi)->format('H:i')
                            : '-';

                        return "
                        <small>
                            <strong>Pagi:</strong> {$pagiStatus} ({$pagiJam})<br>
                            <strong>Sore:</strong> {$soreStatus} ({$soreJam})
                        </small>
                    ";
                    } catch (\Exception $e) {
                        \Log::error('Error in detail_sesi:', ['error' => $e->getMessage()]);
                        return '<small>Error loading details</small>';
                    }
                })
                ->addColumn('bukti_foto', function ($row) {
                    try {
                        // Ambil bukti foto dari salah satu sesi yang ada
                        $presensi = Presensi::where('user_id', $row->user_id)
                            ->where('tanggal_presensi', $row->tanggal_presensi)
                            ->whereNotNull('bukti_foto')
                            ->where('bukti_foto', '!=', 'default.jpg')
                            ->where('bukti_foto', '!=', '')
                            ->first();

                        if ($presensi && $presensi->bukti_foto) {
                            $url = asset('storage/' . $presensi->bukti_foto);
                            return '<a href="' . $url . '" target="_blank"><img src="' . $url . '" width="60" class="rounded"></a>';
                        }
                        return '-';
                    } catch (\Exception $e) {
                        \Log::error('Error in bukti_foto:', ['error' => $e->getMessage()]);
                        return '-';
                    }
                })
                ->rawColumns(['status_badge', 'bukti_foto', 'detail_sesi'])
                ->make(true);
        } catch (\Exception $e) {
            \Log::error('Error in dataSemua:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Cek status presensi hari ini untuk user login
     */
    private function getStatusPresensiHariIni($presensiHariIni, $setting)
    {
        $now         = now();
        $currentTime = $now->format('H:i');

        $pagiData = $presensiHariIni->where('sesi', 'pagi')->first();
        $soreData = $presensiHariIni->where('sesi', 'sore')->first();

        $status = [
            'can_presensi'    => false,
            'current_session' => null,
            'message'         => '',
            'pagi_status'     => $pagiData?->status,
            'sore_status'     => $soreData?->status,
            'pagi_jam'        => $pagiData?->jam_presensi,
            'sore_jam'        => $soreData?->jam_presensi,
        ];

        if (!$setting) {
            $status['message'] = 'Pengaturan presensi belum dikonfigurasi';
            return $status;
        }

        if ($currentTime >= $setting->pagi_mulai && $currentTime < $setting->sore_mulai) {
            $status['current_session'] = 'pagi';
            $status['can_presensi'] = !$pagiData;
            $status['message'] = $pagiData
                ? "Presensi pagi sudah dilakukan ({$pagiData->status})"
                : 'Silakan lakukan presensi pagi';
        } elseif ($currentTime >= $setting->sore_mulai && $currentTime <= $setting->sore_selesai) {
            $status['current_session'] = 'sore';
            $status['can_presensi'] = !$soreData;
            $status['message'] = $soreData
                ? "Presensi sore sudah dilakukan ({$soreData->status})"
                : 'Silakan lakukan presensi sore';
        } else {
            $status['message'] = 'Waktu presensi sudah berakhir untuk hari ini';
        }

        return $status;
    }

    /**
     * Presensi otomatis (kamera) – input: base64 image
     */
    public function PresensiCamera(Request $request)
    {
        Log::info('=== CAMERA PRESENSI AUTO START ===', [
            'user_id' => auth()->id(),
            'request_method' => $request->method(),
        ]);

        try {
            $request->validate([
                'image_data' => 'required|string',
                'keterangan' => 'nullable|string|max:255'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.'
            ], 422);
        }

        $user    = Auth::user();
        $today   = now()->toDateString();
        $now     = now();
        $setting = PresensiSetting::first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Pengaturan presensi belum dikonfigurasi'
            ], 422);
        }

        $currentTime = $now->format('H:i');
        if ($currentTime >= $setting->pagi_mulai && $currentTime < $setting->sore_mulai) {
            $sesi = 'pagi';
        } elseif ($currentTime >= $setting->sore_mulai && $currentTime <= $setting->sore_selesai) {
            $sesi = 'sore';
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Presensi hanya dapat dilakukan pada jam kerja'
            ], 422);
        }

        $existing = Presensi::where('user_id', $user->id)
            ->where('tanggal_presensi', $today)
            ->where('sesi', $sesi)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => "Anda sudah melakukan presensi {$sesi} hari ini"
            ], 422);
        }

        try {
            $imageFile = $this->processBase64Image($request->image_data);
            if (!$imageFile) throw new \Exception('Gagal memproses gambar');

            $fileName = 'camera_' . date('Y-m-d_H-i-s') . '_' . $user->id . '_' . uniqid() . '.jpg';
            $path     = 'uploads/presensi/' . $fileName;

            if (!Storage::disk('public')->put($path, $imageFile)) {
                throw new \Exception('Gagal menyimpan gambar');
            }

            $jamPresensi = $now->format('H:i:s');
            $status      = $this->getStatusByTime($jamPresensi, $sesi, $setting);
            $statusId    = PresensiStatus::where('status', $status)->first()?->id;

            Presensi::create([
                'user_id'            => $user->id,
                'tanggal_presensi'   => $today,
                'sesi'               => $sesi,
                'jam_presensi'       => $jamPresensi,
                'status'             => $status,
                'presensi_status_id' => $statusId,
                'bukti_foto'         => $path,
                'keterangan'         => $request->keterangan ?? "Presensi {$sesi} otomatis",
            ]);

            return response()->json([
                'success' => true,
                'message' => "Presensi {$sesi} berhasil! Status: {$status}",
                'data'    => [
                    'sesi'   => $sesi,
                    'status' => $status,
                    'jam'    => $jamPresensi
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Camera presensi error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan presensi: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Submit Izin/Sakit
     */
    public function submitIzinSakit(Request $request)
    {
        $request->validate([
            'jenis'       => 'required|in:Izin,Sakit',
            'keterangan'  => 'required|string|min:10|max:255',
            'bukti_foto'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user  = Auth::user();
        $today = now()->toDateString();

        $exists = Presensi::where('user_id', $user->id)
            ->where('tanggal_presensi', $today)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Anda sudah melakukan presensi hari ini');
        }

        try {
            $buktiPath = null;
            if ($request->hasFile('bukti_foto')) {
                $buktiPath = $request->file('bukti_foto')->store('uploads/presensi', 'public');
            }

            $jenis    = $request->jenis;
            $statusId = PresensiStatus::where('status', $jenis)->first()?->id;

            foreach (['pagi', 'sore'] as $sesi) {
                Presensi::create([
                    'user_id'            => $user->id,
                    'tanggal_presensi'   => $today,
                    'sesi'               => $sesi,
                    'status'             => $jenis,
                    'presensi_status_id' => $statusId,
                    'bukti_foto'         => $buktiPath,
                    'keterangan'         => $request->keterangan,
                    'jam_presensi'       => null,
                ]);
            }

            return back()->with('success', "Pengajuan {$jenis} berhasil disubmit!");
        } catch (\Exception $e) {
            Log::error('Izin/Sakit submit error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan data. Silakan coba lagi.');
        }
    }

    public function requestApprovalDate(Request $request)
    {
        $request->validate([
            'tanggal_presensi' => 'required|date',
            'requested_status' => 'required|in:Izin,Sakit',
            'keterangan' => 'required|string|min:20',
            'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $userId = Auth::id();
        $tanggal = $request->tanggal_presensi;

        // Cek apakah ada presensi Alpa di tanggal tersebut
        $alpaExists = Presensi::where('user_id', $userId)
            ->where('tanggal_presensi', $tanggal)
            ->where('status', 'Alpa')
            ->exists();

        if (!$alpaExists) {
            return back()->with('error', 'Tidak ada status Alpa pada tanggal tersebut');
        }

        // Cek apakah sudah ada request pending
        $pendingExists = Presensi::where('user_id', $userId)
            ->where('tanggal_presensi', $tanggal)
            ->where('approval_status', 'pending')
            ->exists();

        if ($pendingExists) {
            return back()->with('error', 'Anda sudah memiliki permintaan approval untuk tanggal ini');
        }

        try {
            // Handle upload bukti foto
            $buktiPath = null;
            if ($request->hasFile('bukti_foto')) {
                $buktiPath = $request->file('bukti_foto')->store('uploads/presensi/approval', 'public');
            }

            // Update semua presensi Alpa di tanggal tersebut
            Presensi::where('user_id', $userId)
                ->where('tanggal_presensi', $tanggal)
                ->where('status', 'Alpa')
                ->update([
                    'requested_status' => $request->requested_status,
                    'keterangan' => $request->keterangan,
                    'bukti_foto' => $buktiPath,
                    'approval_status' => 'pending'
                ]);

            return back()->with('success', 'Permintaan perubahan status berhasil dikirim. Menunggu approval admin.');
        } catch (\Exception $e) {
            \Log::error('Request approval error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengirim permintaan. Silakan coba lagi.');
        }
    }
    /**
     * Admin approve/reject permintaan perubahan
     */
    public function processApproval(Request $request, $presensiId)
    {
        if (Auth::user()->group_id !== 2) {
            return back()->with('error', 'Anda tidak memiliki akses untuk melakukan approval');
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes'  => 'nullable|string|max:255'
        ]);

        $presensi = Presensi::findOrFail($presensiId);

        if ($presensi->approval_status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya');
        }

        try {
            if ($request->action === 'approve') {
                $newStatus = $presensi->requested_status;
                $statusId  = PresensiStatus::where('status', $newStatus)->first()?->id;

                $presensi->update([
                    'status'             => $newStatus,
                    'presensi_status_id' => $statusId,
                    'approval_status'    => 'approved',
                    'approval_notes'     => $request->notes,
                    'approved_by'        => Auth::id(),
                    'approved_at'        => now()
                ]);

                Presensi::where('user_id', $presensi->user_id)
                    ->where('tanggal_presensi', $presensi->tanggal_presensi)
                    ->where('approval_status', 'pending')
                    ->where('requested_status', $newStatus)
                    ->update([
                        'status'             => $newStatus,
                        'presensi_status_id' => $statusId,
                        'approval_status'    => 'approved',
                        'approval_notes'     => $request->notes,
                        'approved_by'        => Auth::id(),
                        'approved_at'        => now()
                    ]);

                $message = 'Permintaan perubahan status disetujui';
            } else {
                $alpaStatusId = PresensiStatus::where('status', 'Alpa')->first()?->id;

                $presensi->update([
                    'status'             => 'Alpa',
                    'presensi_status_id' => $alpaStatusId,
                    'approval_status'    => 'rejected',
                    'approval_notes'     => $request->notes,
                    'approved_by'        => Auth::id(),
                    'approved_at'        => now()
                ]);

                Presensi::where('user_id', $presensi->user_id)
                    ->where('tanggal_presensi', $presensi->tanggal_presensi)
                    ->where('approval_status', 'pending')
                    ->update([
                        'status'             => 'Alpa',
                        'presensi_status_id' => $alpaStatusId,
                        'approval_status'    => 'rejected',
                        'approval_notes'     => $request->notes,
                        'approved_by'        => Auth::id(),
                        'approved_at'        => now()
                    ]);

                $message = 'Permintaan perubahan status ditolak';
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Approval process error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses approval. Silakan coba lagi.');
        }
    }

    /**
     * Generate otomatis status Alpa untuk siswa yang belum presensi
     */
    public function generateAlpa()
    {
        if (Auth::user()->group_id !== 2) {
            return back()->with('error', 'Hanya admin yang dapat generate presensi alpa.');
        }

        $today   = now()->toDateString();
        $students = User::where('group_id', 4)->pluck('id'); // siswa = group_id 4 (sesuai kode kamu)
        $presentStudents = Presensi::where('tanggal_presensi', $today)
            ->pluck('user_id')
            ->unique();

        $absentStudents = $students->diff($presentStudents);

        if ($absentStudents->isEmpty()) {
            return back()->with('info', 'Semua siswa sudah melakukan presensi hari ini.');
        }

        $alpaStatusId = PresensiStatus::where('status', 'Alpa')->first()?->id;
        $count = 0;

        foreach ($absentStudents as $userId) {
            foreach (['pagi', 'sore'] as $sesi) {
                Presensi::create([
                    'user_id'            => $userId,
                    'tanggal_presensi'   => $today,
                    'sesi'               => $sesi,
                    'status'             => 'Alpa',
                    'presensi_status_id' => $alpaStatusId,
                    'jam_presensi'       => null,
                    'keterangan'         => 'Generated automatically',
                ]);
                $count++;
            }
        }

        return back()->with('success', "Berhasil generate {$count} presensi alpa untuk " . $absentStudents->count() . " siswa.");
    }

    public function approvalData()
    {
        if (Auth::user()->group_id !== 2) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = Presensi::with(['user.sekolah'])
            ->where('approval_status', 'pending')
            ->orderBy('updated_at', 'desc');

        return DataTables::of($data)
            ->addColumn('tanggal', function ($row) {
                return Carbon::parse($row->tanggal_presensi)->format('d/m/Y');
            })
            ->addColumn('nama', function ($row) {
                return '<strong>' . ($row->user->name ?? '-') . '</strong><br>' .
                    '<small class="text-muted">' . ($row->user->email ?? '-') . '</small>';
            })
            ->addColumn('sekolah', function ($row) {
                return $row->user->sekolah->nama ?? '-';
            })
            ->addColumn('sesi', function ($row) {
                $color = $row->sesi === 'pagi' ? 'info' : 'warning';
                return '<span class="badge bg-' . $color . '">' . ucfirst($row->sesi) . '</span>';
            })
            ->addColumn('status_awal', function ($row) {
                return '<span class="badge bg-danger">Alpa</span>';
            })
            ->addColumn('requested_status', function ($row) {
                $color = $row->requested_status === 'Izin' ? 'info' : 'secondary';
                return '<span class="badge bg-' . $color . '">' . $row->requested_status . '</span>';
            })
            ->addColumn('keterangan', function ($row) {
                $keterangan = $row->keterangan ?? '-';
                if (strlen($keterangan) > 50) {
                    return '<div class="text-truncate" style="max-width: 200px;" title="' . $keterangan . '">' .
                        substr($keterangan, 0, 50) . '...</div>';
                }
                return $keterangan;
            })
            ->addColumn('bukti_foto', function ($row) {
                if ($row->bukti_foto && $row->bukti_foto !== 'default.jpg') {
                    $url = asset('storage/' . $row->bukti_foto);
                    return '<button class="btn btn-sm btn-outline-primary" onclick="showImage(\'' . $url . '\')">' .
                        '<i class="fas fa-eye"></i> Lihat</button>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('waktu_request', function ($row) {
                return '<small>' . $row->updated_at->format('d/m/Y H:i') . '</small>';
            })
            ->addColumn('aksi', function ($row) {
                return '
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-success" onclick="processApproval(' . $row->id . ', \'approve\')" title="Setujui">
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="processApproval(' . $row->id . ', \'reject\')" title="Tolak">
                        <i class="fas fa-times"></i>
                    </button>
                    <button class="btn btn-sm btn-info" onclick="showApprovalModal(' . $row->id . ', \'' .
                    addslashes($row->user->name ?? '') . '\', \'' . ($row->requested_status ?? '') . '\')" title="Detail">
                        <i class="fas fa-info-circle"></i>
                    </button>
                </div>
            ';
            })
            ->rawColumns(['nama', 'sesi', 'status_awal', 'requested_status', 'keterangan', 'bukti_foto', 'waktu_request', 'aksi'])
            ->make(true);
    }

    /**
     * DataTables: Data Approval History
     */
    public function approvalHistory()
    {
        if (Auth::user()->group_id !== 2) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = Presensi::with(['user', 'approvedBy'])
            ->whereIn('approval_status', ['approved', 'rejected'])
            ->where('approved_at', '>=', now()->subDays(7))
            ->orderBy('approved_at', 'desc');

        return DataTables::of($data)
            ->addColumn('tanggal', function ($row) {
                return Carbon::parse($row->tanggal_presensi)->format('d/m/Y');
            })
            ->addColumn('nama', function ($row) {
                return $row->user->name ?? '-';
            })
            ->addColumn('requested_status', function ($row) {
                $color = $row->requested_status === 'Izin' ? 'info' : 'secondary';
                return '<span class="badge bg-' . $color . '">' . ($row->requested_status ?? '-') . '</span>';
            })
            ->addColumn('approval_status', function ($row) {
                $color = $row->approval_status === 'approved' ? 'success' : 'danger';
                $text = $row->approval_status === 'approved' ? 'Disetujui' : 'Ditolak';
                return '<span class="badge bg-' . $color . '">' . $text . '</span>';
            })
            ->addColumn('approval_notes', function ($row) {
                return $row->approval_notes ?? '-';
            })
            ->addColumn('approved_by', function ($row) {
                return $row->approvedBy->name ?? '-';
            })
            ->addColumn('approved_at', function ($row) {
                return $row->approved_at ? '<small>' . Carbon::parse($row->approved_at)->format('d/m/Y H:i') . '</small>' : '-';
            })
            ->rawColumns(['requested_status', 'approval_status', 'approved_at'])
            ->make(true);
    }

    /* ===================== Helpers ===================== */

    private function processBase64Image(string $imageData): ?string
    {
        try {
            Log::info('Processing base64 image', [
                'data_length'       => strlen($imageData),
                'starts_with_data'  => str_starts_with($imageData, 'data:')
            ]);

            if (str_starts_with($imageData, 'data:')) {
                if (!str_contains($imageData, ',')) {
                    throw new \Exception('Invalid data URL format');
                }
                $parts     = explode(',', $imageData, 2);
                $imageData = $parts[1];
            }

            $imageData = preg_replace('/[^A-Za-z0-9+\/=]/', '', $imageData);

            $remainder = strlen($imageData) % 4;
            if ($remainder) {
                $imageData .= str_repeat('=', 4 - $remainder);
            }

            $imageFile = base64_decode($imageData, true);
            if ($imageFile === false) {
                throw new \Exception('Invalid base64 data');
            }

            if (!$this->isValidImageData($imageFile)) {
                throw new \Exception('Invalid image format');
            }

            return $imageFile;
        } catch (\Exception $e) {
            Log::error('Base64 processing error: ' . $e->getMessage());
            return null;
        }
    }

    private function isValidImageData(string $data): bool
    {
        if (strlen($data) < 10) return false;

        $signatures = [
            'JPEG'   => "\xFF\xD8\xFF",
            'PNG'    => "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A",
            'GIF87a' => "GIF87a",
            'GIF89a' => "GIF89a",
        ];

        foreach ($signatures as $signature) {
            if (str_starts_with($data, $signature)) return true;
        }

        return false;
    }

    private function getStatusByTime(string $jamPresensi, string $sesi, $setting)
    {
        if (!$setting) return 'Tepat Waktu';

        $batasWaktu = $sesi === 'pagi' ? $setting->pagi_selesai : $setting->sore_selesai;
        $waktuMulai = $sesi === 'pagi' ? $setting->pagi_mulai : $setting->sore_mulai;

        if (!$batasWaktu || !$waktuMulai) return 'Tepat Waktu';

        $toleransi = $setting->toleransi_telat ?? 15;

        try {
            $waktuPresensi       = Carbon::createFromFormat('H:i:s', $jamPresensi);
            $waktuMulaiCarbon    = Carbon::createFromFormat('H:i:s', $waktuMulai);
            $waktuBatasCarbon    = Carbon::createFromFormat('H:i:s', $batasWaktu);
            $waktuBatasToleransi = Carbon::createFromFormat('H:i:s', $batasWaktu)->addMinutes($toleransi);

            if ($waktuPresensi->lt($waktuMulaiCarbon)) {
                return 'Terlalu Awal';
            }

            if ($waktuPresensi->between($waktuMulaiCarbon, $waktuBatasCarbon)) {
                return 'Tepat Waktu';
            }

            if ($waktuPresensi->between($waktuBatasCarbon->copy()->addSecond(), $waktuBatasToleransi)) {
                return 'Terlambat';
            }

            if ($waktuPresensi->gt($waktuBatasToleransi)) {
                return 'Sangat Terlambat';
            }

            return 'Tepat Waktu';
        } catch (\Exception $e) {
            Log::error('Error in getStatusByTime: ' . $e->getMessage());
            return 'Tepat Waktu';
        }
    }

    private function renderStatusBadge($row): string
    {
        // Prioritaskan status approval jika pending
        if ($row->approval_status === 'pending') {
            $txt   = ($row->requested_status ?? 'Perubahan') . ' (Menunggu)';
            $class = 'warning';
            return '<span class="badge bg-' . $class . '">' . $txt . '</span>';
        }

        // Ambil status dari relasi presensiStatus terlebih dahulu
        $status = $row->presensiStatus?->status ?? $row->status ?? '-';

        $map = [
            'Tepat Waktu'     => 'success',
            'Terlambat'       => 'warning',
            'Sangat Terlambat' => 'danger',
            'Terlalu Awal'    => 'secondary',
            'Izin'            => 'info',
            'Sakit'           => 'secondary',
            'Alpa'            => 'danger',
        ];

        $class = $map[$status] ?? 'secondary';
        return '<span class="badge bg-' . $class . '">' . $status . '</span>';
    }
}
