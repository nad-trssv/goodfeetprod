<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicChatMessageRequest;
use App\Http\Requests\PublicChatStartRequest;
use App\Http\Requests\PublicChatRatingRequest;
use App\Models\CrmConversation;
use App\Services\Crm\CrmChatService;
use App\Services\Crm\CrmChatMessagePresenter;
use App\Services\Crm\CrmChatSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicChatController extends Controller
{
    public function state(CrmChatSettings $settings): JsonResponse { return response()->json($settings->publicState()); }
    public function csrf(): JsonResponse { return response()->json(['token'=>csrf_token()]); }

    public function store(PublicChatStartRequest $request, CrmChatSettings $settings, CrmChatService $chat): JsonResponse
    {
        abort_unless($settings->all()['enabled'], 404);
        $result=$chat->create($request->validated());
        return response()->json(['uuid'=>$result['conversation']->public_uuid,'token'=>$result['token']],201);
    }

    public function show(Request $request, CrmConversation $conversation, CrmChatService $chat, CrmChatSettings $settings, CrmChatMessagePresenter $presenter): JsonResponse
    {
        abort_unless($settings->all()['enabled'], 404);
        $request->validate(['after'=>['nullable','integer','min:0']]);
        $token = (string) $request->header('X-Chat-Token');
        validator(['token'=>$token],['token'=>['required','string','size:64']])->validate();
        $chat->assertToken($conversation,$token);
        $messages=$conversation->messages()
            ->where('id','>',$request->integer('after'))
            ->where('is_public',true)
            ->with('staffSender:id,name')
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->map(fn($message)=>$presenter->present($message,true));
        $rating = null;
        $canRate = false;
        if ($conversation->status === 'closed') {
            $rating = $conversation->rating()->value('rating');
            $canRate = $rating === null && $conversation->messages()->where('sender_type','staff')->exists();
        }
        return response()->json([
            'messages'=>$messages,
            'status'=>$conversation->status,
            'can_rate'=>$canRate,
            'rating'=>$rating,
        ]);
    }

    public function message(PublicChatMessageRequest $request, CrmConversation $conversation, CrmChatService $chat, CrmChatSettings $settings): JsonResponse
    {
        abort_unless($settings->all()['enabled'], 404);
        $message=$chat->publicMessage($conversation,$request->validated('token'),$request->validated('message'));
        return response()->json(['id'=>$message->id],201);
    }

    public function rating(PublicChatRatingRequest $request, CrmConversation $conversation, CrmChatService $chat, CrmChatSettings $settings): JsonResponse
    {
        abort_unless($settings->all()['enabled'], 404);
        $rating=$chat->rate($conversation,$request->validated('token'),$request->integer('rating'));
        return response()->json(['rating'=>$rating->rating],$rating->wasRecentlyCreated ? 201 : 200);
    }
}
