<?php

namespace App\Http\Controllers;

use App\Models\Notifications;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notifications::where('user_id', auth()->user()->id)->get();
        return response()->json([
            'notifications' => $notifications
        ]);
    }
    public function markAsRead($id)
    {
        $notification = Notifications::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllasRead()
    {
        $notifications = Notifications::where('user_id', auth()->id())->get();
        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }
        return response()->json(['success' => true]);
    }

}
