<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppointmentFeedbackRequest;
use App\Models\AppointmentFeedback;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class AppointmentFeedbackController extends Controller
{
    public function show(AppointmentFeedback $feedback): View
    {
        $feedback->load('appointment.service.translations');
        $this->assertAvailable($feedback);
        app()->setLocale($feedback->appointment->client_locale ?: config('app.locale'));

        return view('pages.feedback.show', compact('feedback'));
    }

    public function store(AppointmentFeedbackRequest $request, AppointmentFeedback $feedback): RedirectResponse
    {
        $feedback->load('appointment');
        $this->assertAvailable($feedback);
        app()->setLocale($feedback->appointment->client_locale ?: config('app.locale'));

        DB::transaction(function () use ($feedback, $request) {
            $locked = AppointmentFeedback::whereKey($feedback->id)->lockForUpdate()->firstOrFail();
            if (! $locked->submitted_at) {
                $locked->update([
                    'rating' => $request->integer('rating'),
                    'submitted_at' => now(),
                    'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
                ]);
            }
        });

        return redirect()->route('appointment-feedback.show', $feedback)->with('feedback_saved', true);
    }

    private function assertAvailable(AppointmentFeedback $feedback): void
    {
        abort_unless(
            $feedback->appointment
            && $feedback->appointment->appointment_end->isPast()
            && $feedback->appointment->status === 'completed',
            404,
        );
    }
}
