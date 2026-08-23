<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SiteDomainEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = mb_strtolower(trim((string) $value));
        $domain = str_contains($email, '@') ? substr(strrchr($email, '@'), 1) : '';

        if ($domain === '' || ! str_contains($domain, '.') || in_array($domain, config('technical_support.free_email_domains', []), true)) {
            $fail(__('admin_support.sender_domain_invalid'));

            return;
        }

        $siteHost = mb_strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        $siteHost = preg_replace('/^www\./', '', $siteHost) ?: '';

        if (($siteHost === '' || $this->isLocalHost($siteHost)) && app()->bound('request')) {
            $requestHost = mb_strtolower((string) request()->getHost());
            if (! $this->isLocalHost($requestHost)) {
                $siteHost = preg_replace('/^www\./', '', $requestHost) ?: $siteHost;
            }
        }

        if ($siteHost === '' || $this->isLocalHost($siteHost)) {
            return;
        }

        $belongsToSite = $domain === $siteHost
            || str_ends_with($domain, '.'.$siteHost)
            || str_ends_with($siteHost, '.'.$domain);

        if (! $belongsToSite) {
            $fail(__('admin_support.sender_domain_mismatch', ['domain' => $siteHost]));
        }
    }

    private function isLocalHost(string $host): bool
    {
        return $host === 'localhost'
            || $host === '127.0.0.1'
            || $host === '::1'
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.local');
    }
}
