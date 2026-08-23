<?php

namespace App\Services\Messaging;

use App\Models\TriggerMessageAttempt;

class DeliveryStatusService
{
    public function record(string $provider, string $externalId, string $status, ?string $error = null): void
    {
        $attempt = TriggerMessageAttempt::query()
            ->where('provider', $provider)->where('external_id', $externalId)
            ->latest('id')->first();
        if (! $attempt) {
            return;
        }

        $dispatch = $attempt->dispatch;
        if (in_array($status, ['delivered', 'read', 'seen'], true)) {
            $attempt->update(['status'=>'delivered', 'delivered_at'=>now(), 'error'=>null]);
            $dispatch->update(['status'=>'delivered', 'delivered_at'=>now(), 'last_error'=>null]);
            return;
        }
        if ($status === 'failed') {
            $attempt->update(['status'=>'failed', 'error'=>$error ?: 'Provider reported delivery failure']);
            if (! $dispatch->expires_at || $dispatch->expires_at->isFuture()) {
                $dispatch->update(['status'=>'pending', 'scheduled_at'=>now(), 'last_error'=>$attempt->error]);
            } else {
                $dispatch->update(['status'=>'failed', 'last_error'=>$attempt->error]);
            }
        }
    }
}
