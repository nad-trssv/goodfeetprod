<?php

namespace App\Services\Crm;

use App\Models\CrmChatStaff;
use App\Models\CrmConversation;
use App\Models\CrmMessage;
use App\Models\CrmConversationRating;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\CrmChatMessageNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class CrmChatService
{
    public function __construct(private readonly CrmChatSettings $settings) {}

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
        abort_if($conversation->status === 'closed', 409, __('crm.conversation_closed'));
        $customer = Auth::guard('customer')->user();
        $message = DB::transaction(function () use ($conversation, $customer, $body) {
            $message = $conversation->messages()->create(['sender_type'=>$customer?'customer':'visitor','sender_id'=>$customer?->id,'body'=>$body]);
            $conversation->update(['last_message_at'=>now(),'status'=>'open','closed_at'=>null]);
            return $message;
        });
        $this->notifyStaff($conversation->refresh());
        return $message;
    }

    /**
     * @return array<int, CrmMessage>
     */
    public function staffMessage(CrmConversation $conversation, User $staff, string $body): array
    {
        return DB::transaction(function () use ($conversation, $staff, $body) {
            $messages = [];

            if ($this->settings->all()['notify_client_staff_events'] && $this->shouldAnnounce($conversation, $staff)) {
                $messages[] = $conversation->messages()->create([
                    'sender_type'=>'system',
                    'event_type'=>'staff_joined',
                    'sender_id'=>$staff->id,
                    'metadata'=>['staff_id'=>$staff->id,'staff_name'=>$staff->name],
                    'is_public'=>true,
                    'body'=>__('crm.staff_joined_internal',['name'=>$staff->name]),
                ]);
            }

            $message = $conversation->messages()->create(['sender_type'=>'staff','sender_id'=>$staff->id,'body'=>$body]);
            $conversation->update(['last_message_at'=>now(),'status'=>'open','closed_at'=>null]);
            $messages[] = $message;

            return $messages;
        });
    }

    public function transfer(CrmConversation $conversation, User $actor, User $target): void
    {
        DB::transaction(function () use ($conversation, $actor, $target) {
            $notifyClient = $this->settings->all()['notify_client_staff_events'];
            $conversation->update(['assigned_to_user_id'=>$target->id,'status'=>'open','last_message_at'=>now()]);
            $conversation->messages()->create([
                'sender_type'=>'system','sender_id'=>$actor->id,
                'event_type'=>'conversation_transferred',
                'metadata'=>['target_staff_id'=>$target->id,'target_staff_name'=>$target->name],
                'is_public'=>$notifyClient,
                'body'=>__('crm.transferred_to',['name'=>$target->name]),
            ]);
            $target->notify(new CrmChatMessageNotification($conversation));
        });
    }

    public function close(CrmConversation $conversation, User $staff): void
    {
        DB::transaction(function () use ($conversation, $staff) {
            $locked = CrmConversation::whereKey($conversation->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'closed') return;

            $locked->messages()->create([
                'sender_type'=>'system',
                'event_type'=>'conversation_closed',
                'sender_id'=>$staff->id,
                'metadata'=>['staff_id'=>$staff->id,'staff_name'=>$staff->name],
                'is_public'=>true,
                'body'=>__('crm.conversation_closed_by',['name'=>$staff->name]),
            ]);
            $locked->update(['status'=>'closed','closed_at'=>now(),'last_message_at'=>now()]);
        });
    }

    public function rate(CrmConversation $conversation, string $token, int $rating): CrmConversationRating
    {
        $this->assertToken($conversation, $token);

        return DB::transaction(function () use ($conversation, $rating) {
            $locked = CrmConversation::whereKey($conversation->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->status === 'closed', 422, __('crm.rating_closed_only'));

            $existing = $locked->rating()->first();
            if ($existing) return $existing;

            $lastStaffMessage = $locked->messages()
                ->where('sender_type','staff')
                ->with('staffSender:id,name')
                ->latest('id')
                ->first();
            abort_unless($lastStaffMessage?->staffSender, 422, __('crm.rating_no_staff'));

            return $locked->rating()->create([
                'staff_user_id'=>$lastStaffMessage->sender_id,
                'staff_name'=>$lastStaffMessage->staffSender->name,
                'rating'=>$rating,
            ]);
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

    private function shouldAnnounce(CrmConversation $conversation, User $staff): bool
    {
        $lastEvent = $conversation->messages()
            ->whereIn('event_type', ['staff_joined', 'conversation_transferred'])
            ->latest('id')
            ->first(['event_type', 'metadata']);

        return $lastEvent?->event_type !== 'staff_joined'
            || (int) ($lastEvent->metadata['staff_id'] ?? 0) !== (int) $staff->id;
    }
}
