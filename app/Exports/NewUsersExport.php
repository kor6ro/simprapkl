<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class NewUsersExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $users;

    /**
     * Kita akan mengirim data user (termasuk password mentah) ke class ini.
     */
    public function __construct(Collection $users)
    {
        $this->users = $users;
    }

    /**
     * Mengembalikan data yang akan ditulis ke Excel.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->users;
    }

    /**
     * Mendefinisikan header untuk setiap kolom di Excel.
     */
    public function headings(): array
    {
        return [
            'Nama Lengkap',
            'Username',
            'Password',
        ];
    }
}
