@section('title', __('policy.title'))
<x-app-layout>
    @php
        $companyName = $settings['company_name'] ?? config('app.name');
        $registrationNumber = $settings['company_registration_number'] ?? '—';
        $companyEmail = $settings['company_email'] ?? '';
    @endphp
    <section class="py-10 overflow-hidden">
        <div class="container">
            <article class="contacts mb-4">
                <h1 class="fw-semibold fc_main mt-4 mb-1 ff_secondary">{{ __('policy.title') }}</h1>
                <p class="fw-light fc_secondary ff_secondary">{{ __('policy.last_updated', ['date' => '28.03.2025']) }}</p>
                <p class="fw-light fc_secondary ff_secondary">{{ __('policy.company_identity', ['company' => $companyName, 'registration_number' => $registrationNumber]) }}</p>
                <p class="fw-light fc_secondary ff_secondary">{{ __('policy.intro', ['company' => $companyName, 'url' => config('app.url')]) }}</p>
                <p class="fw-light fc_secondary ff_secondary">{{ __('policy.changes', ['company' => $companyName]) }}</p>

                <h2 class="fw-semibold fc_main mt-4 mb-1 ff_secondary">{{ __('policy.collected_title') }}</h2>
                <p class="fw-light fc_secondary ff_secondary">{{ __('policy.collected_intro') }}</p>
                <ul>
                    @foreach(['collected_name', 'collected_phone', 'collected_email', 'collected_other'] as $key)
                        <li class="ff_secondary">{{ __('policy.'.$key) }}</li>
                    @endforeach
                </ul>

                <h2 class="fw-semibold fc_main mt-4 mb-1 ff_secondary">{{ __('policy.security_title') }}</h2>
                <p class="fw-light fc_secondary ff_secondary">{{ __('policy.security_text') }}</p>

                <h2 class="fw-semibold fc_main mt-4 mb-1 ff_secondary">{{ __('policy.contacts_title') }}</h2>
                <p class="fw-light fc_secondary ff_secondary">{{ __('policy.contacts_intro') }}</p>
                <ul>
                    @foreach(['contacts_information', 'contacts_inaccuracy', 'contacts_usage', 'contacts_noncompliance'] as $key)
                        <li class="ff_secondary">{{ __('policy.'.$key) }}</li>
                    @endforeach
                </ul>
                <p class="fw-light fc_secondary ff_secondary">{{ __('policy.contacts_outro', ['company' => $companyName]) }}</p>
                @if($companyEmail)
                    <p class="fw-light fc_secondary ff_secondary">{{ __('policy.email', ['email' => $companyEmail]) }}</p>
                @endif

                <h2 class="fw-semibold fc_main mt-4 mb-1 ff_secondary">{{ __('policy.deletion_title') }}</h2>
                <p class="fw-light fc_secondary ff_secondary">{{ __('policy.deletion_text') }}</p>
            </article>
        </div>
    </section>
</x-app-layout>
