<?php

namespace App\Services\Crm;

use App\Models\CrmChatStaff;
use App\Models\CrmConversation;
use App\Models\CrmMessage;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\CrmChatMessageNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class CrmChatService
{
    public function create(array $data): array
    {
        $token = Str::random(64);
        $conversation = DB::transaction(function () use ($data, $token) {
            $customer = Auth::guard('customer')->user();
            $conversation = CrmConversation::create([
                'access_token_hash'=>hash('sha256',$token), 'customer_id'=>$customer?->id,
                'visitor_name'=>$customer?->full_name ?: ($data['name'] ?? null),
                'visitor_email'=>$customer?->email ?: ($data['email'] ?? null),
                'visitor_phone'=>$customer?->phone ?: ($data['phone'] ?? null),
                'last_message_at'=>now(),
            ]);
            $conversation->messages()->create([
                'sender_type'=>$customer ? 'customer':'visitor', 'sender_id'=>$customer?->id, 'body'=>$data['message'],
            ]);
            return $conversation;
        });
        $this->notifyStaff($conversation);
        return ['conversation'=>$conversation,'token'=>$token];
    }

    public function publicMessage(CrmConversation $conversation, string $token, string $body): CrmMessage
    {
        $this->assertToken($conversation, $token);
        $customer = Auth::guard('customer')->user();
        $message = DB::transaction(function () use ($conversation, $customer, $body) {
            $message = $conversation->messages()->create(['sender_type'=>$customer?'customer':'visitor','sender_id'=>$customer?->id,'body'=>$body]);
            $conversation->update(['last_message_at'=>now(),'status'=>'open','closed_at'=>null]);
            return $message;
        });
        $this->notifyStaff($conversation->refresh());
        return $message;
    }

    public function staffMessage(CrmConversation $conversation, User $staff, string $body): CrmMessage
    {
        return DB::transaction(function () use ($conversation, $staff, $body) {
            $message = $conversation->messages()->create(['sender_type'=>'staff','sender_id'=>$staff->id,'body'=>$body]);
            $conversation->update(['last_message_at'=>now(),'status'=>'open','closed_at'=>null]);
            return $message;
        });
    }

    public function transfer(CrmConversation $conversation, User $actor, User $target): void
    {
        DB::transaction(function () use ($conversation, $actor, $target) {
            $conversation->update(['assigned_to_user_id'=>$target->id,'status'=>'open']);
            $conversation->messages()->create([
                'sender_type'=>'system','sender_id'=>$actor->id,
                'body'=>__('crm.transferred_to',['name'=>$target->name]),
            ]);
            $target->notify(new CrmChatMessageNotification($conversation));
        });
    }

    public function assertToken(CrmConversation $conversation, string $token): void
    {
        abort_unless(hash_equals($conversation->access_token_hash, hash('sha256',$token)), 403);
    }

    private function notifyStaff(CrmConversation $conversation): void
    {
        $ids = CrmChatStaff::where('is_enabled',true)->where('must_answer',true)->pluck('user_id');
        if ($conversation->assigned_to_user_id) $ids->push($conversation->assigned_to_user_id);
        $recipients = User::whereIn('id',$ids->unique())->get()->filter(fn(User $user)=>$user->hasPermission('crm.chat.view'));
        if ($recipients->isNotEmpty()) Notification::send($recipients, new CrmChatMessageNotification($conversation));
    }
}
