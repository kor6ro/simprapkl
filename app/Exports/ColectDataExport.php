<?php

namespace App\Exports;

use App\Models\ColectData;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ColectDataExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    private int $rowNumber = 0;
    protected $filterBulan;
    protected $filterNamaSiswa;

    public function __construct($filterBulan = null, $filterNamaSiswa = null)
    {
        $this->filterBulan = $filterBulan;
        $this->filterNamaSiswa = $filterNamaSiswa;
    }

    public function query()
    {
        $query = ColectData::query()->with('user')->orderBy('tanggal', 'desc');

        if ($this->filterBulan) {
            try {
                $date = Carbon::parse($this->filterBulan);
                $query->whereYear('tanggal', $date->year)->whereMonth('tanggal', $date->month);
            } catch (\Exception $e) { /* Abaikan */
            }
        }

        if ($this->filterNamaSiswa) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->filterNamaSiswa . '%');
            });
        }

        if (auth()->check() && auth()->user()->group_id == 4) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Collector',
            'Nama Customer',
            'No Telp',
            'Alamat Customer',
            'Provider yg Digunakan',
            'Kelebihan',
            'Kekurangan',
        ];
    }

    public function map($data): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            Carbon::parse($data->tanggal)->format('d/m/Y'),
            $data->user->name ?? 'N/A',
            $data->nama_cus,
            $data->no_telp,
            $data->alamat_cus,
            $data->provider_sekarang,
            $data->kelebihan,
            $data->kekurangan,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // 1. Atur Judul dan Lebar Kolom
                $sheet->insertNewRowBefore(1, 3);
                $title = 'LIST HASIL COLLECT DATA';
                if ($this->filterNamaSiswa) $title .= ' - ' . strtoupper($this->filterNamaSiswa);
                $sheet->mergeCells('A1:I1');
                $sheet->setCellValue('A1', $title);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $subtitle = Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY');
                if ($this->filterBulan) $subtitle = 'Periode: ' . Carbon::parse($this->filterBulan)->locale('id')->isoFormat('MMMM YYYY');
                $sheet->mergeCells('A2:I2');
                $sheet->setCellValue('A2', $subtitle);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(18);
                $sheet->getColumnDimension('F')->setWidth(40);
                $sheet->getColumnDimension('G')->setWidth(22);
                $sheet->getColumnDimension('H')->setWidth(30);
                $sheet->getColumnDimension('I')->setWidth(30);

                // 2. Atur Style Header Biru
                $sheet->getStyle('A4:I4')->getFont()->setBold(true)->getColor()->setARGB(Color::COLOR_WHITE);
                $sheet->getStyle('A4:I4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF4F81BD');

                // 3. Hitung baris terakhir
                $lastRow = 4 + $this->rowNumber;

                // 4. [PERUBAHAN] Terapkan Zebra Stripes (Pewarnaan provider dihapus)
                for ($row = 5; $row <= $lastRow; $row++) {
                    if ($row % 2 == 1) { // Baris ganjil (5, 7, 9...) diberi warna abu-abu
                        $sheet->getStyle('A' . $row . ':I' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
                    }
                }

                // 5. Terapkan Border ke seluruh tabel
                if ($lastRow > 4) {
                    $sheet->getStyle('A4:I' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
            },
        ];
    }
}