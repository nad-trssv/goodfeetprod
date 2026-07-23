<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function status(Request $request): JsonResponse
    {
        $notifications = $request->user()->unreadNotifications()->latest()->limit(6)->get();

        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
            'html' => view('admin.notifications._dropdown-items', compact('notifications'))->render(),
        ]);
    }

    public function index(Request $request): View
    {
        $query = $request->user()->notifications()->latest();
        if ($request->filled('event')) {
            $query->where('data->event', $request->string('event'));
        }
        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }

        return view('admin.notifications.index', [
            'notifications' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $item->markAsRead();

        return redirect()->to($this->target($item->data));
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }

    private function target(array $data): string
    {
        if (($data['event'] ?? null) === 'reschedule_requested') {
            return route('reschedule.index');
        }

        return isset($data['appointment_id'])
            ? route('calendar.show', ['appointment' => $data['appointment_id']])
            : route('notifications.index');
    }
}
