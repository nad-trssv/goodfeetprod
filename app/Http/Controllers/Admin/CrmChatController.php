<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CrmChatReplyRequest;
use App\Http\Requests\CrmChatTransferRequest;
use App\Models\CrmConversation;
use App\Models\User;
use App\Services\Crm\CrmChatAccess;
use App\Services\Crm\CrmChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CrmChatController extends Controller
{
    public function index(Request $request, CrmChatAccess $access): View
    {
        $conversations=$access->visible($request->user())->with(['customer','assignee:id,name','messages'=>fn($q)=>$q->latest()->limit(1)])
            ->when($request->input('status','open')!=='all',fn($q)=>$q->where('status',$request->input('status','open')))
            ->orderByDesc('last_message_at')->paginate(30)->withQueryString();
        return view('admin.crm.chat.index',compact('conversations'));
    }

    public function show(Request $request, CrmConversation $conversation, CrmChatAccess $access): View
    {
        abort_unless($access->canView($request->user(),$conversation),403);
        $access->markRead($request->user(),$conversation);
        $conversation->load(['customer','assignee:id,name,profile_photo_path','messages'=>fn($q)=>$q->with('staffSender:id,name,profile_photo_path')->orderBy('id')]);
        $staff=User::with('role')->get()->filter->isStaff()->sortBy('name')->values();
        return view('admin.crm.chat.show',compact('conversation','staff'));
    }

    public function messages(Request $request, CrmConversation $conversation, CrmChatAccess $access): JsonResponse
    {
        abort_unless($access->canView($request->user(),$conversation),403);
        $messages=$conversation->messages()->where('id','>',$request->integer('after'))->with('staffSender:id,name')->orderBy('id')->limit(100)->get();
        $access->markRead($request->user(),$conversation);
        return response()->json(['messages'=>$messages->map(fn($m)=>['id'=>$m->id,'sender'=>$m->sender_type,'sender_name'=>$m->staffSender?->name,'body'=>$m->body,'time'=>$m->created_at->toIso8601String()])]);
    }

    public function reply(CrmChatReplyRequest $request, CrmConversation $conversation, CrmChatAccess $access, CrmChatService $chat): JsonResponse
    {
        abort_unless($access->canView($request->user(),$conversation),403);
        $message=$chat->staffMessage($conversation,$request->user(),$request->validated('message'));
        $access->markRead($request->user(),$conversation);
        return response()->json(['id'=>$message->id],201);
    }

    public function transfer(CrmChatTransferRequest $request, CrmConversation $conversation, CrmChatAccess $access, CrmChatService $chat): JsonResponse
    {
        abort_unless($access->canView($request->user(),$conversation),403);
        $target=User::with('role.permissions')->findOrFail($request->integer('user_id'));
        abort_unless($target->isStaff(),422,__('crm.not_staff'));
        $chat->transfer($conversation,$request->user(),$target);
        return response()->json(['assigned_to'=>$target->name]);
    }

    public function close(Request $request, CrmConversation $conversation, CrmChatAccess $access): JsonResponse
    {
        abort_unless($request->user()->hasPermission('crm.chat.reply') && $access->canView($request->user(),$conversation),403);
        $conversation->update(['status'=>'closed','closed_at'=>now()]);
        return response()->json(['status'=>'closed']);
    }

    public function status(Request $request, CrmChatAccess $access): JsonResponse
    {
        return response()->json(['count'=>$access->unreadCount($request->user())]);
    }
}
