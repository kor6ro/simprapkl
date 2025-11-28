<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Presensi;
use App\Models\Laporan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class RekapitulasiPklExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    // ... (properti dan constructor tidak berubah) ...
    protected $sekolahId;
    protected $programId;
    protected $periodePklId; // Tambahkan properti ini
    protected $tanggalAwal;
    protected $tanggalAkhir;
    protected $liburNasional;
    protected $semuaTanggal;
    protected $dataSiswa;
    protected $normalColumns = [];
    protected $mergedHolidayColumns = [];

    public function __construct($filters)
    {
        $this->sekolahId = $filters['sekolah_id'] ?? null;
        $this->programId = $filters['program_keahlian_id'] ?? null;
        $this->periodePklId = $filters['periode_pkl_id'] ?? null; // Tambahkan baris ini

        $start = $filters['tanggal_awal'] ?? Carbon::now()->startOfWeek();
        $end = $filters['tanggal_akhir'] ?? Carbon::now()->endOfWeek();

        $this->tanggalAwal = Carbon::parse($start);
        $this->tanggalAkhir = Carbon::parse($end);

        $this->liburNasional = [
            '2025-08-23' => 'LIBUR',
        ];

        $this->semuaTanggal = CarbonPeriod::create($this->tanggalAwal, '1 day', $this->tanggalAkhir)->toArray();
        $this->dataSiswa = $this->fetchData();
    }


private function fetchData()
    {
        $siswaQuery = User::where('group_id', 4);
        if ($this->sekolahId) {
            $siswaQuery->where('sekolah_id', $this->sekolahId);
        }
        if ($this->programId) {
            $siswaQuery->where('program_keahlian_id', $this->programId);
        }
        if ($this->periodePklId) {
            $siswaQuery->whereHas('periodePkl', function ($q) {
                $q->where('periode_pkl.id', $this->periodePklId);
            });
        }
        $listSiswa = $siswaQuery->with('sekolah')->orderBy('name')->get();

        if ($listSiswa->isEmpty()) {
            return collect();
        }

        $siswaIds = $listSiswa->pluck('id');

        $presensiData = Presensi::whereIn('user_id', $siswaIds)
            ->whereBetween('presensi_at', [$this->tanggalAwal, $this->tanggalAkhir])
            ->get()
            ->groupBy('user_id');

        $laporanData = Laporan::whereIn('user_id', $siswaIds)
            ->whereBetween('created_at', [$this->tanggalAwal, $this->tanggalAkhir])
            ->get()
            ->groupBy('user_id');

        // Definisikan grup status
        $statusAlpa = ['Alpa'];
        $statusHadir = ['Tepat Waktu', 'Terlambat', 'Sangat Terlambat', 'Terlalu Awal', 'Hadir'];
        $statusSakit = ['Sakit'];
        $statusIzin = ['Izin', 'Izin Mendesak', 'Izin Terencana'];

        // (Logika untuk mergedHolidayColumns tidak berubah)
        foreach ($this->semuaTanggal as $tanggalObj) {
            $tanggal = $tanggalObj->toDateString();
            $isLiburNasional = isset($this->liburNasional[$tanggal]);
            $isAkhirPekan = $tanggalObj->isWeekend();

            $hasActivity = ($isLiburNasional || $isAkhirPekan) && (
                Presensi::where('presensi_at', $tanggal)->whereIn('user_id', $siswaIds)->exists() ||
                Laporan::whereDate('created_at', $tanggal)->whereIn('user_id', $siswaIds)->exists()
            );

            if (($isLiburNasional || $isAkhirPekan) && !$hasActivity) {
                $this->mergedHolidayColumns[] = $tanggal;
            } else {
                $this->normalColumns[] = $tanggal;
            }
        }

        $dataExport = collect();
        foreach ($listSiswa as $siswa) {
            $dataSiswa = [
                'nama' => $siswa->name,
                'rekap' => []
            ];

            $presensiSiswa = $presensiData->get($siswa->id, collect());
            $laporanSiswa = $laporanData->get($siswa->id, collect());

            foreach ($this->semuaTanggal as $tanggalObj) {
                $tanggal = $tanggalObj->toDateString();
                $isMergedHoliday = in_array($tanggal, $this->mergedHolidayColumns);
                $presensiHariIni = $presensiSiswa->filter(fn ($p) => $p->presensi_at->isSameDay($tanggal));

                $absenStatus = '-';

                if ($isMergedHoliday) {
                    $absenStatus = isset($this->liburNasional[$tanggal]) ? $this->liburNasional[$tanggal] : 'LIBUR';
                } elseif ($presensiHariIni->isNotEmpty()) {
                    $isAlpa = $presensiHariIni->contains(fn($p) => in_array($p->status, $statusAlpa));
                    $isHadir = $presensiHariIni->contains(fn($p) => in_array($p->status, $statusHadir));
                    $isSakit = $presensiHariIni->contains(fn($p) => in_array($p->status, $statusSakit));
                    $isIzin = $presensiHariIni->contains(fn($p) => in_array($p->status, $statusIzin));
                    
                    if ($isAlpa) {
                        $absenStatus = 'A';
                    } elseif ($isHadir) {
                        $absenStatus = 'H';
                    } elseif ($isSakit) {
                        $absenStatus = 'S';
                    } elseif ($isIzin) {
                        $absenStatus = 'I';
                    }
                } elseif ($tanggalObj->isWeekend()) {
                    $absenStatus = 'LIBUR';
                }

                $laporanPadaHariItu = $laporanSiswa->first(fn($l) => $l->created_at->isSameDay($tanggal));
                $laporanStatus = $isMergedHoliday ? '' : ($laporanPadaHariItu ? 'OK' : '-');

                $dataSiswa['rekap'][$tanggal]['absen'] = $absenStatus;
                $dataSiswa['rekap'][$tanggal]['laporan'] = $laporanStatus;
            }
            $dataExport->push($dataSiswa);
        }
        return $dataExport;
    }
    public function collection()
    {
        return $this->dataSiswa;
    }

    public function headings(): array
    {
        $row1 = ['NAMA SISWA'];
        $row2 = [''];

        foreach ($this->semuaTanggal as $tanggalObj) {
            $tanggal = $tanggalObj->format('j');
            $tanggalString = $tanggalObj->toDateString();

            $isMergedHoliday = in_array($tanggalString, $this->mergedHolidayColumns);
            if ($isMergedHoliday) {
                $row1[] = $tanggal;
                $row1[] = ''; // Merge
                $row2[] = 'Absen';
                $row2[] = 'Laporan';
            } else {
                $row1[] = $tanggal;
                $row1[] = '';
                $row2[] = 'Absen';
                $row2[] = 'Laporan';
            }
        }
        return [$row1, $row2];
    }

    public function map($siswa): array
    {
        $rowData = [$siswa['nama']];
        foreach ($this->semuaTanggal as $tanggalObj) {
            $tanggal = $tanggalObj->toDateString();
            $data = $siswa['rekap'][$tanggal];
            $isMergedHoliday = in_array($tanggal, $this->mergedHolidayColumns);

            if ($isMergedHoliday) {
                $rowData[] = $data['absen'];
                $rowData[] = '';
            } else {
                $rowData[] = $data['absen'];
                $rowData[] = $data['laporan'];
            }
        }
        return $rowData;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $sheet->getDefaultRowDimension()->setRowHeight(18);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(18);

        $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);

        $sheet->getStyle('A1:' . $highestColumn . '2')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle('A1:A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFC000');
        $sheet->mergeCells('A1:A2');

        $colIndex = 2;
        foreach ($this->semuaTanggal as $tanggalObj) {
            $tanggal = $tanggalObj->toDateString();
            $colLetter = $this->_columnLetterFromIndex($colIndex);

            $isMergedHoliday = in_array($tanggal, $this->mergedHolidayColumns);

            if ($isMergedHoliday) {
                $sheet->getStyle($colLetter . '1:' . $this->_columnLetterFromIndex($colIndex + 1) . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFC000');
                $sheet->mergeCells($colLetter . '1:' . $this->_columnLetterFromIndex($colIndex + 1) . '1');

                $sheet->getStyle($colLetter . '2:' . $this->_columnLetterFromIndex($colIndex + 1) . '2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9D9D9');
                $sheet->mergeCells($colLetter . '2:' . $this->_columnLetterFromIndex($colIndex + 1) . '2');

                // Loop through each row to merge and style holiday cells
                for ($row = 3; $row <= $highestRow; $row++) {
                    $sheet->mergeCells($colLetter . $row . ':' . $this->_columnLetterFromIndex($colIndex + 1) . $row);
                    
                    $sheet->getStyle($colLetter . $row)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9D9D9']],
                    ]);
                    // ===================================
                    
                    $sheet->getStyle($colLetter . $row)->getAlignment()->setWrapText(true);
                }
                
                $absenValue = isset($this->liburNasional[$tanggal]) ? $this->liburNasional[$tanggal] : 'LIBUR';
                $sheet->setCellValue($colLetter . '3', $absenValue);

                $colIndex += 2;
            } else {
                $sheet->getStyle($colLetter . '1:' . $this->_columnLetterFromIndex($colIndex + 1) . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFC000');
                $sheet->mergeCells($colLetter . '1:' . $this->_columnLetterFromIndex($colIndex + 1) . '1');

                $isNormalHoliday = in_array($tanggal, $this->normalColumns) && (isset($this->liburNasional[$tanggal]) || $tanggalObj->isWeekend());
                $subHeaderColor = $isNormalHoliday ? 'D9D9D9' : 'D9D9D9';
                $sheet->getStyle($colLetter . '2:' . $this->_columnLetterFromIndex($colIndex + 1) . '2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($subHeaderColor);

                $row = 3;
                foreach ($this->dataSiswa as $siswa) {
                    $data = $siswa['rekap'][$tanggal];

                    $absenColor = 'FFFFFF';
                    switch ($data['absen']) {
                        case 'H':
                            $absenColor = 'B7E1CD';
                            break;
                        case 'I':
                        case 'S':
                            $absenColor = 'B9DDE6';
                            break;
                        case 'A':
                            $absenColor = 'FF6161';
                            break;
                        case 'C':
                            $absenColor = 'FBF4A2';
                            break;
                        case 'LIBUR':
                            $absenColor = 'D9D9D9';
                            break;
                    }
                    $sheet->getStyle($this->_columnLetterFromIndex($colIndex) . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($absenColor);
                    $sheet->getStyle($this->_columnLetterFromIndex($colIndex) . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $laporanColor = 'FFFFFF';
                    if ($data['laporan'] == 'OK') {
                        $laporanColor = 'B9DDE6';
                    }
                    if ($data['absen'] == 'LIBUR') {
                        $laporanColor = 'D9D9D9';
                    }

                    $sheet->getStyle($this->_columnLetterFromIndex($colIndex + 1) . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($laporanColor);
                    $sheet->getStyle($this->_columnLetterFromIndex($colIndex + 1) . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $row++;
                }
                $colIndex += 2;
            }
        }
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 25];
        $colIndex = 2;
        foreach ($this->semuaTanggal as $tanggalObj) {
            $tanggalString = $tanggalObj->toDateString();
            $isMergedHoliday = in_array($tanggalString, $this->mergedHolidayColumns);

            if ($isMergedHoliday) {
                $widths[$this->_columnLetterFromIndex($colIndex)] = 10;
                $colIndex += 2;
            } else {
                $widths[$this->_columnLetterFromIndex($colIndex)] = 8;
                $widths[$this->_columnLetterFromIndex($colIndex + 1)] = 8;
                $colIndex += 2;
            }
        }
        return $widths;
    }

    private function _columnLetterFromIndex(int $index): string
    {
        $string = '';
        while ($index > 0) {
            $temp = ($index - 1) % 26;
            $string = chr(65 + $temp) . $string;
            $index = ($index - $temp - 1) / 26;
        }
        return $string;
    }
}