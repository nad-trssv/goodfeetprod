<?php

namespace App\Services;

use App\Mail\TechnicalSupportMail;
use App\Models\SiteSettings;
use App\Models\User;
use App\Rules\SiteDomainEmail;
use App\Support\ActivityLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TechnicalSupportService
{
    public function send(User $employee, array $data): void
    {
        $senderEmail = $this->setting('technical_support_sender_email');
        $validator = Validator::make(
            ['sender_email' => $senderEmail],
            ['sender_email' => ['required', 'email:rfc', new SiteDomainEmail]],
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'support' => __('admin_support.configuration_error'),
            ]);
        }

        $companyName = str_replace(["\r", "\n"], ' ', $this->setting('company_name') ?: config('app.name'));
        $pageUrl = $this->safePageUrl($data['page_url'] ?? null);

        Mail::to(config('technical_support.recipient'))->send(new TechnicalSupportMail(
            employee: $employee,
            senderEmail: $senderEmail,
            companyName: $companyName,
            categoryLabel: __('admin_support.categories.'.$data['category']),
            requestSubject: str_replace(["\r", "\n"], ' ', trim($data['subject'])),
            requestMessage: trim($data['message']),
            pageUrl: $pageUrl,
        ));

        ActivityLog::make(
            'technical_support.sent',
            'Technical support request sent',
            'technical_support',
            actor: $employee,
            properties: ['category' => $data['category'], 'subject' => $data['subject']],
        );
    }

    private function setting(string $key): ?string
    {
        $payload = SiteSettings::query()->where('key', $key)->value('payload');
        $value = is_string($payload) ? json_decode($payload, true) : null;

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function safePageUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $urlHost = mb_strtolower((string) parse_url($url, PHP_URL_HOST));
        $requestHost = mb_strtolower((string) request()->getHost());

        return $urlHost === $requestHost ? $url : null;
    }
}
