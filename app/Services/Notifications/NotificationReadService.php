<?php

namespace App\Services\Notifications;

use App\Models\Appointments;
use App\Models\CrmConversation;
use App\Models\User;

class NotificationReadService
{
    public function conversation(User $user, CrmConversation $conversation): int
    {
        return $user->unreadNotifications()
            ->where('data->conversation_uuid', $conversation->public_uuid)
            ->update(['read_at'=>now()]);
    }

    public function appointment(User $user, Appointments $appointment): int
    {
        return $user->unreadNotifications()
            ->where(function ($query) use ($appointment) {
                $query->where('data->appointment_uuid', $appointment->public_uuid)
                    ->orWhere('data->appointment_id', $appointment->id);
            })
            ->update(['read_at'=>now()]);
    }

    public function event(User $user, string $event): int
    {
        return $user->unreadNotifications()
            ->where('data->event', $event)
            ->update(['read_at'=>now()]);
    }
}
