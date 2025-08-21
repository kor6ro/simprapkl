<?php

namespace App\Providers;

use App\Models\User; // <-- Pastikan ini ada
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gate untuk user dengan role tertentu
        Gate::define('is-admin', fn(User $user) => $user->group?->nama === 'Admin');
        Gate::define('is-pembimbing', fn(User $user) => $user->group?->nama === 'Pembimbing');
        Gate::define('is-siswa', fn(User $user) => $user->group?->nama === 'Siswa');
        Gate::define('is-karyawan', fn(User $user) => $user->group?->nama === 'Karyawan');

        // Gate untuk menu Master Data
        Gate::define('view-master-data', function (User $user) {
            return $user->group?->nama === 'Admin' || $user->group?->nama === 'Karyawan';
        });

        // Gate untuk menu Manajemen User
        Gate::define('manage-users', function (User $user) {
            return in_array($user->group?->nama, ['Admin', 'Pembimbing', 'Karyawan']);
        });

        // Gate untuk Presensi
        Gate::define('manage-presensi', fn(User $user) => $user->group?->nama === 'Admin');
        Gate::define('view-all-presensi', fn(User $user) => in_array($user->group?->nama, ['Admin', 'Pembimbing', 'Karyawan']));
        Gate::define('input-presensi', fn(User $user) => $user->group?->nama === 'Siswa');
        Gate::define('validate-presensi', fn(User $user) => $user->group?->nama === 'Admin');
    }
}
