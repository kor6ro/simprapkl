<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UserCredentialsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $users;

    public function __construct(Collection $users)
    {
        $this->users = $users;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Memetakan data dari koleksi ke format array yang diinginkan untuk Excel
        return $this->users->map(function ($user) {
            return [
                'Nama Lengkap'  => $user['name'],
                'Username'      => $user['username'],
                'Password Baru' => $user['new_password'],
                'Sekolah'       => $user['sekolah'],
            ];
        });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Mendefinisikan judul untuk setiap kolom di file Excel
        return [
            'Nama Lengkap',
            'Username',
            'Password Baru',
            'Sekolah',
        ];
    }
}