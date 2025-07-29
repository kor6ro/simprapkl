<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('isRole')) {
    function isRole($name)
    {
        return Auth::check()
            && Auth::user()->group
            && Auth::user()->group->nama === $name;
    }
}

if (!function_exists('isDeveloper')) {
    function isDeveloper()
    {
        return Auth::check() && Auth::user()->group_id === 1;
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin()
    {
        return Auth::check() && Auth::user()->group_id === 2;
    }
}

if (!function_exists('isPembimbing')) {
    function isPembimbing()
    {
        return Auth::check() && Auth::user()->group_id === 3;
    }
}

if (!function_exists('isSiswa')) {
    function isSiswa()
    {
        return Auth::check() && Auth::user()->group_id === 4;
    }
}

if (!function_exists('canManagePresensi')) {
    function canManagePresensi()
    {
        return isDeveloper() || isAdmin();
    }
}

if (!function_exists('canViewAllPresensi')) {
    function canViewAllPresensi()
    {
        return isDeveloper() || isAdmin() || isPembimbing();
    }
}

if (!function_exists('canInputPresensi')) {
    function canInputPresensi()
    {
        return isSiswa();
    }
}

if (!function_exists('canValidatePresensi')) {
    function canValidatePresensi()
    {
        return isDeveloper() || isAdmin();
    }
}
