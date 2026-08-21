<?php

namespace App\Services\Crm;

use App\Models\CrmChatStaff;
use App\Models\CrmConversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CrmChatAccess
{
    public function canView(User $user, CrmConversation $conversation): bool
    {
        if (! $user->hasPermission('crm.chat.view')) return false;
        if ($user->hasAllAppointmentsScope() || (int)$conversation->assigned_to_user_id === (int)$user->id) return true;
        $staff = CrmChatStaff::where('user_id', $user->id)->where('is_enabled', true)->first();
        return (bool)($staff?->can_view_history || $staff?->must_answer);
    }

    public function visible(User $user): Builder
    {
        $query = CrmConversation::query();
        if ($user->hasAllAppointmentsScope()) return $query;
        $staff = CrmChatStaff::where('user_id', $user->id)->where('is_enabled', true)->first();
        if ($staff?->can_view_history || $staff?->must_answer) return $query;
        return $query->where('assigned_to_user_id', $user->id);
    }

    public function unreadCount(User $user): int
    {
        if (! $user->hasPermission('crm.chat.view')) return 0;
        return $this->visible($user)
            ->whereRaw("COALESCE((SELECT MAX(cm.id) FROM crm_messages cm WHERE cm.conversation_id = crm_conversations.id AND cm.sender_type IN ('visitor','customer')), 0) > COALESCE((SELECT ccr.last_read_message_id FROM crm_conversation_reads ccr WHERE ccr.conversation_id = crm_conversations.id AND ccr.user_id = ? LIMIT 1), 0)", [$user->id])
            ->count();
    }

    public function markRead(User $user, CrmConversation $conversation): void
    {
        $lastId = $conversation->messages()->max('id');
        $now = now();
        DB::table('crm_conversation_reads')->upsert([
            [
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'last_read_message_id' => $lastId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['conversation_id', 'user_id'], ['last_read_message_id', 'updated_at']);
    }
}
