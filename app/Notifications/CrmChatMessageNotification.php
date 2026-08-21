<?php

namespace App\Notifications;

use App\Models\CrmConversation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CrmChatMessageNotification extends Notification
{
    use Queueable;
    public function __construct(private readonly CrmConversation $conversation) {}
    public function via(object $notifiable): array { return ['database']; }
    public function toArray(object $notifiable): array
    {
        return [
            'event'=>'crm_chat_message',
            'conversation_uuid'=>$this->conversation->public_uuid,
            'client_name'=>$this->conversation->customer?->full_name ?: $this->conversation->visitor_name ?: __('crm.visitor'),
            'service_name'=>__('crm.new_message'),
            'url'=>route('crm.chat.show', $this->conversation),
        ];
    }
}
