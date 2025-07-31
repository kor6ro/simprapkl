<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com/)**
-   **[Tighten Co.](https://tighten.co)**
-   **[WebReinvent](https://webreinvent.com/)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
-   **[Cyber-Duck](https://cyber-duck.co.uk)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Jump24](https://jump24.co.uk)**
-   **[Redberry](https://redberry.international/laravel/)**
-   **[Active Logic](https://activelogic.com)**
-   **[byte5](https://byte5.de)**
-   **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Simpra PKL - Sistem Presensi

Sistem presensi untuk Praktik Kerja Lapangan (PKL) dengan fitur manajemen waktu presensi yang fleksibel, sistem otomatis untuk telat dan bolos, serta role-based access control.

## Fitur Utama

### 1. Presensi Setting

-   **Manajemen Waktu Presensi**: Atur 4 variabel waktu untuk sesi pagi dan sore
-   **Contoh Setting**:
    -   Sesi Pagi: 07:00 - 08:15
    -   Sesi Sore: 16:00 - 17:00
-   **Aktivasi Setting**: Hanya satu setting yang dapat aktif pada satu waktu
-   **Validasi Waktu**: Presensi hanya dapat dilakukan dalam rentang waktu yang ditentukan

### 2. Presensi Jenis

-   **Jenis Presensi**: Hadir, Telat, Izin, Sakit, Bolos
-   **Butuh Bukti**: Beberapa jenis presensi memerlukan upload bukti
-   **Otomatis**: Jenis presensi tertentu dapat diatur otomatis (telat, bolos)

### 3. Presensi

-   **Input Presensi**: Masukkan data presensi dengan validasi waktu
-   **Upload Bukti**: Upload gambar bukti presensi (jika diperlukan)
-   **Validasi Duplikasi**: Mencegah presensi ganda untuk user, tanggal, dan sesi yang sama
-   **Sistem Otomatis**: Deteksi otomatis telat dan bolos
-   **Role-Based Access**: Akses berbeda berdasarkan group user

### 4. Presensi Gambar

-   **Manajemen Bukti**: Upload dan kelola bukti presensi
-   **Preview Gambar**: Tampilkan preview bukti presensi
-   **Validasi Format**: Hanya menerima format gambar (JPG, PNG, GIF)

### 5. Sistem Otomatis Telat & Bolos

-   **Telat Otomatis**: Jika presensi dilakukan di luar jam yang ditentukan
-   **Bolos Otomatis**: Jika salah satu sesi (pagi/sore) tidak diisi pada hari tersebut
-   **Pengecekan Manual**: Tombol untuk menjalankan pengecekan otomatis
-   **Job Queue**: Sistem background job untuk pengecekan otomatis

### 6. Role-Based Access Control

-   **Group 1 (Developer)**: Hidden, akses penuh, fitur developer tersembunyi
-   **Group 2 (Admin)**: Bisa melihat semua, validasi absen, cek otomatis
-   **Group 3 (Pembimbing)**: Hanya bisa melihat semua presensi siswa
-   **Group 4 (Siswa PKL)**: Hanya bisa melihat presensi sendiri, dan hanya siswa yang bisa input presensi
-   **⚠️ Penting**: Presensi hanya untuk siswa (Group 4), admin/pembimbing/developer tidak ikut presensi

## Sistem Role

### **🎯 Akses Berdasarkan Group:**

#### **Group 1 - Developer**

-   **Status**: Hidden (tidak terlihat di interface)
-   **Akses**: Penuh ke semua fitur
-   **Fitur Khusus**:
    -   Cek presensi otomatis
    -   Validasi semua presensi
    -   Akses ke semua data

#### **Group 2 - Admin**

-   **Status**: Visible dengan badge admin
-   **Akses**:
    -   Melihat semua presensi
    -   Validasi presensi
    -   Cek presensi otomatis
    -   Input presensi untuk user lain
-   **Fitur Khusus**: Tombol "Cek Presensi Otomatis"

#### **Group 3 - Pembimbing**

-   **Status**: Visible dengan badge pembimbing
-   **Akses**:
    -   Melihat semua presensi siswa
    -   Tidak bisa input/edit presensi
    -   Tidak bisa validasi
-   **Fitur Khusus**: View-only untuk monitoring

#### **Group 4 - Siswa PKL**

-   **Status**: Visible dengan badge siswa
-   **Akses**:
    -   Melihat presensi sendiri saja
    -   Input presensi untuk diri sendiri
    -   Edit/hapus presensi sendiri
-   **Fitur Khusus**: Form input presensi otomatis set user_id

### **🔧 Helper Functions:**

```php
// Role checking
isDeveloper()     // Group 1
isAdmin()         // Group 2
isPembimbing()    // Group 3
isSiswa()         // Group 4

// Permission checking
canManagePresensi()    // Developer + Admin
canViewAllPresensi()   // Developer + Admin + Pembimbing
canInputPresensi()     // Siswa only
canValidatePresensi()  // Developer + Admin
```

### **⚠️ Penting: Presensi Hanya untuk Siswa**

-   **Siswa (Group 4)**: Satu-satunya yang dapat melakukan presensi
-   **Admin/Developer**: Hanya dapat input presensi untuk siswa lain (untuk keperluan admin)
-   **Pembimbing**: Hanya dapat melihat presensi siswa untuk monitoring
-   **Sistem Otomatis**: Hanya mengecek siswa (Group 4) untuk telat/bolos
-   **Tidak Ada**: Admin bolos, pembimbing bolos, atau developer bolos

## Sistem Otomatis

### Cara Kerja Sistem Otomatis:

#### 1. **Telat Otomatis**

-   **Trigger**: Presensi dilakukan setelah jam selesai yang ditentukan
-   **Contoh**: Setting pagi 07:00-08:15, jika presensi jam 09:00 → otomatis jadi telat
-   **Aksi**: Sistem mengubah jenis presensi menjadi "telat" dan menambahkan catatan

#### 2. **Bolos Otomatis**

-   **Trigger**: Tidak ada presensi untuk salah satu sesi pada hari tersebut
-   **Contoh**: Tidak ada presensi pagi → otomatis buat presensi "bolos" untuk sesi pagi
-   **Aksi**: Sistem membuat presensi "bolos" dengan jam selesai sesi tersebut

#### 3. **Pengecekan Manual**

-   **Tombol "Cek Presensi Otomatis"**: Di halaman presensi (Admin/Developer only)
-   **Command**: `php artisan presensi:check-otomatis`
-   **Fungsi**: Menjalankan pengecekan untuk semua user

### Implementasi Teknis:

#### Job Queue

```php
// app/Jobs/CheckPresensiOtomatis.php
class CheckPresensiOtomatis implements ShouldQueue
{
    public function handle()
    {
        // Cek semua user
        // Buat bolos jika tidak ada presensi
        // Ubah jadi telat jika presensi terlambat
    }
}
```

#### Controller Logic

```php
// Saat input presensi
if ($isTelat) {
    $PresensiStatusId = $jenisTelat->id;
    $keterangan .= ' (Otomatis telat)';
    $statusVerifikasi = 'valid';
}
```

## Struktur Database

### Tabel `presensi_setting`

```sql
- id (Primary Key)
- pagi_mulai (Time)
- pagi_selesai (Time)
- sore_mulai (Time)
- sore_selesai (Time)
- created_at, updated_at
```

### Tabel `presensi_status`

```sql
- id (Primary Key)
- nama (String)
- butuh_bukti (Boolean)
- otomatis (Boolean)
- created_at, updated_at
```

### Tabel `presensi`

```sql
- id (Primary Key)
- user_id (Foreign Key)
- presensi_status_id (Foreign Key)
- tanggal_presensi (Date)
- sesi (Enum: pagi, sore)
- jam_presensi (Time)
- keterangan (Text)
- status_verifikasi (Enum)
- catatan_verifikasi (Text)
- created_at, updated_at
```

### Tabel `presensi_gambar`

```sql
- id (Primary Key)
- presensi_id (Foreign Key)
- bukti (String - Path to image)
- created_at, updated_at
```

### Tabel `user` & `group`

```sql
- user.group_id (Foreign Key ke group.id)
- group.id = 1 (Developer)
- group.id = 2 (Admin)
- group.id = 3 (Pembimbing)
- group.id = 4 (Siswa PKL)
```

## Cara Penggunaan

### 1. Setup Presensi Setting

1. Buka menu "Setting Presensi"
2. Klik "Tambah" untuk membuat setting baru
3. Atur waktu untuk sesi pagi dan sore
4. Aktifkan setting dengan klik tombol "Aktifkan"

### 2. Input Presensi (Siswa)

1. Login sebagai siswa (Group 4)
2. Buka menu "Presensi"
3. Klik "Input Presensi"
4. Form akan otomatis set user_id ke siswa yang login
5. Pilih jenis presensi, sesi, dan upload bukti jika diperlukan
6. Sistem akan otomatis mendeteksi telat jika di luar jam

### 3. Validasi Presensi (Admin)

1. Login sebagai admin (Group 2)
2. Buka menu "Presensi"
3. Lihat semua presensi siswa
4. Edit presensi untuk validasi status
5. Klik "Cek Presensi Otomatis" untuk menjalankan pengecekan

### 4. Monitoring (Pembimbing)

1. Login sebagai pembimbing (Group 3)
2. Buka menu "Presensi"
3. Lihat semua presensi siswa untuk monitoring
4. Tidak bisa edit/input presensi

### 5. Cek Presensi Otomatis

1. Login sebagai admin/developer
2. Di halaman presensi, klik tombol "Cek Presensi Otomatis"
3. Sistem akan membuat presensi bolos untuk sesi yang kosong
4. Sistem akan mengubah presensi telat yang belum terdeteksi

### 6. Kelola Bukti Presensi

1. Buka menu "Presensi Gambar"
2. Upload bukti untuk presensi yang memerlukan
3. Preview dan kelola bukti yang sudah diupload

## API Endpoints

### Presensi Setting

-   `GET /api/presensi-setting/active` - Get active presensi setting

### Presensi

-   `GET /api/presensi/statistics` - Get presensi statistics
-   `POST /admin/presensi/check-automatic` - Run automatic presensi check (Admin/Developer only)

## Validasi Sistem

### 1. Validasi Waktu

-   Presensi hanya dapat dilakukan dalam rentang waktu yang ditentukan
-   Sistem otomatis mendeteksi telat jika presensi di luar jam

### 2. Validasi Duplikasi

-   Mencegah presensi ganda untuk kombinasi user, tanggal, dan sesi yang sama
-   Sistem akan menampilkan pesan error jika mencoba presensi ganda

### 3. Validasi Bukti

-   Jenis presensi tertentu memerlukan upload bukti
-   Form akan menampilkan indikator wajib untuk jenis presensi yang memerlukan bukti

### 4. Sistem Otomatis

-   **Telat**: Deteksi otomatis saat presensi di luar jam
-   **Bolos**: Buat otomatis jika tidak ada presensi untuk sesi tertentu
-   **Pengecekan**: Manual trigger untuk menjalankan pengecekan otomatis

### 5. Role-Based Access

-   **Siswa**: Hanya bisa lihat dan input presensi sendiri
-   **Pembimbing**: Hanya bisa lihat semua presensi siswa
-   **Admin**: Bisa lihat semua, validasi, dan cek otomatis
-   **Developer**: Hidden, akses penuh

## Dashboard

Dashboard menampilkan:

-   Total users
-   Presensi hari ini
-   Presensi pagi dan sore
-   Daftar presensi terbaru
-   Setting presensi aktif

## Command Line

### Pengecekan Otomatis

```bash
# Jalankan pengecekan otomatis
php artisan presensi:check-otomatis

# Atau gunakan tombol di interface (Admin/Developer only)
```

## Teknologi

-   **Framework**: Laravel 10
-   **Database**: MySQL
-   **Frontend**: Bootstrap, jQuery, DataTables
-   **File Upload**: Laravel Storage
-   **Validation**: Laravel Validator
-   **API**: Laravel API Resources
-   **Job Queue**: Laravel Jobs & Queues
-   **Scheduling**: Laravel Task Scheduling
-   **Role Management**: Custom Helper Functions

## Instalasi

1. Clone repository
2. Install dependencies: `composer install`
3. Copy `.env.example` to `.env`
4. Configure database in `.env`
5. Run migrations: `php artisan migrate:fresh --seed`
6. Start server: `php artisan serve`

## Default Data

Setelah menjalankan seeder, sistem akan memiliki:

-   Default presensi setting (07:00-08:15 pagi, 16:00-17:00 sore)
-   Jenis presensi: Hadir, Telat, Izin, Sakit, Bolos
-   Sample users dengan group yang berbeda:
    -   Developer (Group 1)
    -   Admin (Group 2)
    -   Pembimbing (Group 3)
    -   Siswa PKL (Group 4)

## Cron Job (Opsional)

Untuk menjalankan pengecekan otomatis secara berkala, tambahkan ke crontab:

```bash
# Setiap hari jam 23:00
0 23 * * * cd /path/to/project && php artisan presensi:check-otomatis
```

## Keamanan

### Role-Based Security

-   **Siswa**: Terisolasi, hanya akses data sendiri
-   **Pembimbing**: Read-only access untuk monitoring
-   **Admin**: Full access dengan validasi
-   **Developer**: Hidden access untuk maintenance

### Data Protection

-   Validasi input di semua level
-   CSRF protection
-   File upload validation
-   SQL injection prevention
-   XSS protection
