<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
    $user = Auth::user();

        // Ambil semua notifikasi, urutkan dari yang terbaru, dan gunakan pagination
    $notifications = $user->notifications()->latest()->paginate(20);

        // Tandai semua notifikasi yang belum dibaca sebagai telah dibaca
        // saat pengguna membuka halaman riwayat
    $user->unreadNotifications->markAsRead();

    return view('notifications.index', compact('notifications'));
    }
    /**
     * Fetch unread and recent read notifications for the logged-in user.
     */
    public function fetch()
    {
        $user = Auth::user();
        $unreadNotifications = $user->unreadNotifications;

        return response()->json([
            'unread' => $unreadNotifications,
            'unread_count' => $unreadNotifications->count(),
        ]);
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAsRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }
}