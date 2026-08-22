<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CrmChatReplyRequest;
use App\Http\Requests\CrmChatTransferRequest;
use App\Models\CrmConversation;
use App\Models\User;
use App\Services\Crm\CrmChatAccess;
use App\Services\Crm\CrmChatMessagePresenter;
use App\Services\Crm\CrmChatService;
use App\Services\Notifications\NotificationReadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Collection;

class CrmChatController extends Controller
{
    public function index(Request $request, CrmChatAccess $access): View
    {
        $conversations=$access->visible($request->user())->with(['customer','assignee:id,name','messages'=>fn($q)=>$q->latest()->limit(1)])
            ->when($request->input('status','open')!=='all',fn($q)=>$q->where('status',$request->input('status','open')))
            ->orderByDesc('last_message_at')->paginate(30)->withQueryString();
        return view('admin.crm.chat.index',compact('conversations'));
    }

    public function show(Request $request, CrmConversation $conversation, CrmChatAccess $access, NotificationReadService $notifications): View
    {
        abort_unless($access->canView($request->user(),$conversation),403);
        $access->markRead($request->user(),$conversation);
        $notifications->conversation($request->user(),$conversation);
        $conversationHistory=$this->conversationHistory($conversation);
        $staff=User::with('role')->get()->filter->isStaff()->sortBy('name')->values();
        return view('admin.crm.chat.show',compact('conversation','conversationHistory','staff'));
    }

    public function messages(Request $request, CrmConversation $conversation, CrmChatAccess $access, CrmChatMessagePresenter $presenter, NotificationReadService $notifications): JsonResponse
    {
        abort_unless($access->canView($request->user(),$conversation),403);
        $messages=$conversation->messages()->where('id','>',$request->integer('after'))->with('staffSender:id,name')->orderBy('id')->limit(100)->get();
        $access->markRead($request->user(),$conversation);
        $notifications->conversation($request->user(),$conversation);
        return response()->json(['messages'=>$messages->map(fn($message)=>$presenter->present($message))]);
    }

    public function reply(CrmChatReplyRequest $request, CrmConversation $conversation, CrmChatAccess $access, CrmChatService $chat, CrmChatMessagePresenter $presenter): JsonResponse
    {
        abort_unless($access->canView($request->user(),$conversation),403);
        $messages=$chat->staffMessage($conversation,$request->user(),$request->validated('message'));
        $access->markRead($request->user(),$conversation);
        return response()->json([
            'id' => end($messages)->id,
            'messages' => collect($messages)->map(fn($message)=>$presenter->present($message))->values(),
        ], 201);
    }

    public function transfer(CrmChatTransferRequest $request, CrmConversation $conversation, CrmChatAccess $access, CrmChatService $chat): JsonResponse
    {
        abort_unless($access->canView($request->user(),$conversation),403);
        $target=User::with('role.permissions')->findOrFail($request->integer('user_id'));
        abort_unless($target->isStaff(),422,__('crm.not_staff'));
        $chat->transfer($conversation,$request->user(),$target);
        return response()->json(['assigned_to'=>$target->name]);
    }

    public function close(Request $request, CrmConversation $conversation, CrmChatAccess $access, CrmChatService $chat): JsonResponse
    {
        abort_unless($request->user()->hasPermission('crm.chat.reply') && $access->canView($request->user(),$conversation),403);
        $chat->close($conversation,$request->user());
        return response()->json(['status'=>'closed']);
    }

    public function status(Request $request, CrmChatAccess $access): JsonResponse
    {
        return response()->json(['count'=>$access->unreadCount($request->user())]);
    }

    private function conversationHistory(CrmConversation $conversation): Collection
    {
        $history=collect();
        $cursor=$conversation;
        $seen=[];

        while($cursor && count($seen)<20 && !in_array($cursor->id,$seen,true)){
            $seen[]=$cursor->id;
            $cursor->load([
                'customer',
                'assignee:id,name,profile_photo_path',
                'messages'=>fn($query)=>$query->with('staffSender:id,name,profile_photo_path')->orderBy('id'),
            ]);
            $history->prepend($cursor);
            $cursor=$cursor->previous_conversation_id
                ? CrmConversation::find($cursor->previous_conversation_id)
                : null;
        }

        return $history->values();
    }
}
