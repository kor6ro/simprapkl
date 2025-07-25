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
