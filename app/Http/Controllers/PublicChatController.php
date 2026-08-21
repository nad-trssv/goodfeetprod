<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicChatMessageRequest;
use App\Http\Requests\PublicChatStartRequest;
use App\Models\CrmConversation;
use App\Services\Crm\CrmChatService;
use App\Services\Crm\CrmChatSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicChatController extends Controller
{
    public function state(CrmChatSettings $settings): JsonResponse { return response()->json($settings->publicState()); }

    public function store(PublicChatStartRequest $request, CrmChatSettings $settings, CrmChatService $chat): JsonResponse
    {
        abort_unless($settings->all()['enabled'], 404);
        $result=$chat->create($request->validated());
        return response()->json(['uuid'=>$result['conversation']->public_uuid,'token'=>$result['token']],201);
    }

    public function show(Request $request, CrmConversation $conversation, CrmChatService $chat, CrmChatSettings $settings): JsonResponse
    {
        abort_unless($settings->all()['enabled'], 404);
        $request->validate(['token'=>['required','string','size:64'],'after'=>['nullable','integer','min:0']]);
        $chat->assertToken($conversation,$request->string('token'));
        $messages=$conversation->messages()->where('id','>',$request->integer('after'))->orderBy('id')->limit(100)->get()->map(fn($message)=>[
            'id'=>$message->id,'sender'=>$message->sender_type,'body'=>$message->body,'time'=>$message->created_at->toIso8601String(),
        ]);
        return response()->json(['messages'=>$messages,'status'=>$conversation->status]);
    }

    public function message(PublicChatMessageRequest $request, CrmConversation $conversation, CrmChatService $chat, CrmChatSettings $settings): JsonResponse
    {
        abort_unless($settings->all()['enabled'], 404);
        $message=$chat->publicMessage($conversation,$request->validated('token'),$request->validated('message'));
        return response()->json(['id'=>$message->id],201);
    }
}
