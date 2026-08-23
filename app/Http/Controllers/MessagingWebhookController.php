<?php

namespace App\Http\Controllers;

use App\Models\MessagingIntegration;
use App\Services\Messaging\DeliveryStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MessagingWebhookController extends Controller
{
    public function verifyWhatsApp(Request $request): Response
    {
        $integration = MessagingIntegration::where('provider', 'whatsapp')->firstOrFail();
        $expected = (string) (($integration->credentials ?? [])['webhook_verify_token'] ?? '');
        abort_unless(
            $request->query('hub.mode') === 'subscribe'
            && filled($expected)
            && hash_equals($expected, (string) $request->query('hub.verify_token')),
            403,
        );

        return response((string) $request->query('hub.challenge'), 200)->header('Content-Type', 'text/plain');
    }

    public function whatsapp(Request $request, DeliveryStatusService $statuses): JsonResponse
    {
        $integration = MessagingIntegration::where('provider', 'whatsapp')->firstOrFail();
        $secret = (string) (($integration->credentials ?? [])['app_secret'] ?? '');
        $signature = (string) $request->header('X-Hub-Signature-256');
        abort_unless(filled($secret) && hash_equals('sha256='.hash_hmac('sha256', $request->getContent(), $secret), $signature), 403);

        foreach ((array) data_get($request->json()->all(), 'entry', []) as $entry) {
            foreach ((array) data_get($entry, 'changes', []) as $change) {
                foreach ((array) data_get($change, 'value.statuses', []) as $status) {
                    $statuses->record('whatsapp', (string) ($status['id'] ?? ''), (string) ($status['status'] ?? ''), data_get($status, 'errors.0.title'));
                }
            }
        }

        return response()->json(['received'=>true]);
    }

    public function viber(Request $request, DeliveryStatusService $statuses): JsonResponse
    {
        $integration = MessagingIntegration::where('provider', 'viber')->firstOrFail();
        $token = (string) (($integration->credentials ?? [])['auth_token'] ?? '');
        $signature = (string) $request->header('X-Viber-Content-Signature');
        abort_unless(filled($token) && hash_equals(hash_hmac('sha256', $request->getContent(), $token), $signature), 403);

        $event = (string) $request->input('event');
        if (in_array($event, ['delivered', 'seen', 'failed'], true) && filled($request->input('message_token'))) {
            $statuses->record('viber', (string) $request->input('message_token'), $event, $request->input('desc'));
        }

        return response()->json(['status'=>0, 'status_message'=>'ok']);
    }
}
