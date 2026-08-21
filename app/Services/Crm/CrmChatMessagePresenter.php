<?php

namespace App\Services\Crm;

use App\Models\CrmMessage;

class CrmChatMessagePresenter
{
    public function present(CrmMessage $message, bool $forPublic = false): array
    {
        $message->loadMissing('staffSender:id,name');

        return [
            'id' => $message->id,
            'sender' => $message->sender_type,
            'kind' => $message->sender_type === 'system' ? 'system' : 'message',
            'sender_name' => $message->sender_type === 'staff' ? $message->staffSender?->name : null,
            'body' => $forPublic ? $this->publicBody($message) : $message->body,
            'time' => $message->created_at->toIso8601String(),
        ];
    }

    private function publicBody(CrmMessage $message): string
    {
        return match ($message->event_type) {
            'staff_joined' => __('crm.client_staff_joined', [
                'name' => $message->metadata['staff_name'] ?? $message->staffSender?->name ?? __('admin_nav.staff'),
            ]),
            'conversation_transferred' => __('crm.client_transfer_notice'),
            'conversation_closed' => __('crm.client_conversation_closed'),
            default => $message->body,
        };
    }
}
