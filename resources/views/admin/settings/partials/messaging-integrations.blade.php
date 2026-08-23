@include('admin.settings.partials.trigger-automations')

<div class="alert alert-subtle-info border d-flex gap-3 align-items-start">
  <span data-feather="info" class="flex-shrink-0 mt-1"></span>
  <div><strong>{{ __('admin_messaging.future_title') }}</strong><div>{{ __('admin_messaging.future_hint') }}</div></div>
</div>

<div class="row g-4">
  @foreach(config('messaging_integrations.providers') as $provider => $definition)
    @php
      $integration = $messagingIntegrations->get($provider) ?? new \App\Models\MessagingIntegration(['provider' => $provider]);
      $providerSettings = $integration->settings ?? [];
      $hasAnyCredential = collect($definition['credentials'])->contains(fn ($key) => $integration->hasCredential($key));
      $submittedProvider = old('provider_form') === $provider;
    @endphp
    <div class="col-12">
      <div class="card border h-100">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
          <div>
            <h5 class="mb-1">{{ __('admin_messaging.providers.'.$provider.'.name') }}</h5>
            <p class="text-body-secondary fs-9 mb-0">{{ __('admin_messaging.providers.'.$provider.'.description') }}</p>
          </div>
          <div class="d-flex gap-2">
            <span class="badge badge-phoenix badge-phoenix-{{ $integration->exists && $integration->isConfigured() ? 'success' : 'warning' }}">{{ $integration->exists && $integration->isConfigured() ? __('admin_messaging.configured') : __('admin_messaging.incomplete') }}</span>
            <span class="badge badge-phoenix badge-phoenix-{{ $integration->enabled ? 'primary' : 'secondary' }}">{{ $integration->enabled ? __('admin_messaging.enabled') : __('admin_messaging.disabled') }}</span>
          </div>
        </div>
        <div class="card-body">
          <div class="alert alert-subtle-secondary py-2 fs-9">{{ __('admin_messaging.providers.'.$provider.'.prerequisites') }}</div>
          <form method="POST" action="{{ route('settings.messaging.update', $provider) }}" autocomplete="off">
            @csrf
            @method('PUT')
            <input type="hidden" name="settings_tab" value="integrations">
            <input type="hidden" name="provider_form" value="{{ $provider }}">
            <input type="hidden" name="enabled" value="0">
            <div class="form-check form-switch mb-4">
              <input class="form-check-input" id="{{ $provider }}_enabled" name="enabled" type="checkbox" value="1" @checked($submittedProvider ? old('enabled') : $integration->enabled)>
              <label class="form-check-label fw-semibold" for="{{ $provider }}_enabled">{{ __('admin_messaging.enable_provider') }}</label>
              <div class="form-text">{{ __('admin_messaging.enable_hint') }}</div>
            </div>

            <div class="row g-3">
              @foreach($definition['settings'] as $key => $type)
                <div class="col-12 col-lg-6">
                  <label class="form-label" for="{{ $provider }}_{{ $key }}">{{ __('admin_messaging.providers.'.$provider.'.fields.'.$key.'.label') }}</label>
                  @if($type === 'json')
                    <textarea class="form-control font-monospace {{ $submittedProvider && $errors->has('settings.'.$key) ? 'is-invalid' : '' }}" id="{{ $provider }}_{{ $key }}" name="settings[{{ $key }}]" rows="3" maxlength="4000">{{ $submittedProvider ? old('settings.'.$key) : ($providerSettings[$key] ?? '') }}</textarea>
                  @else
                    <input class="form-control {{ $submittedProvider && $errors->has('settings.'.$key) ? 'is-invalid' : '' }}" id="{{ $provider }}_{{ $key }}" name="settings[{{ $key }}]" type="{{ $type === 'url' ? 'url' : 'text' }}" value="{{ $submittedProvider ? old('settings.'.$key) : ($providerSettings[$key] ?? '') }}" maxlength="{{ $type === 'url' ? 2048 : 255 }}">
                  @endif
                  <div class="form-text">{{ __('admin_messaging.providers.'.$provider.'.fields.'.$key.'.hint') }}</div>
                  @if($submittedProvider && $errors->has('settings.'.$key))<div class="invalid-feedback">{{ $errors->first('settings.'.$key) }}</div>@endif
                </div>
              @endforeach

              @foreach($definition['credentials'] as $key)
                <div class="col-12 col-lg-6">
                  <label class="form-label" for="{{ $provider }}_{{ $key }}">{{ __('admin_messaging.providers.'.$provider.'.fields.'.$key.'.label') }}</label>
                  <div class="input-group">
                    <input class="form-control {{ $submittedProvider && $errors->has('credentials.'.$key) ? 'is-invalid' : '' }}" id="{{ $provider }}_{{ $key }}" name="credentials[{{ $key }}]" type="password" value="" maxlength="4096" autocomplete="new-password" placeholder="{{ $integration->hasCredential($key) ? __('admin_messaging.secret_saved') : __('admin_messaging.secret_missing') }}">
                    @if($integration->hasCredential($key))<span class="input-group-text text-success"><span data-feather="check" style="width:1rem"></span></span>@endif
                  </div>
                  <div class="form-text">{{ __('admin_messaging.providers.'.$provider.'.fields.'.$key.'.hint') }} {{ __('admin_messaging.secret_hint') }}</div>
                  @if($submittedProvider && $errors->has('credentials.'.$key))<div class="invalid-feedback d-block">{{ $errors->first('credentials.'.$key) }}</div>@endif
                </div>
              @endforeach
            </div>

            @if($hasAnyCredential)
              <div class="form-check mt-4">
                <input type="hidden" name="clear_credentials" value="0">
                <input class="form-check-input" id="{{ $provider }}_clear_credentials" name="clear_credentials" type="checkbox" value="1">
                <label class="form-check-label text-danger" for="{{ $provider }}_clear_credentials">{{ __('admin_messaging.clear_credentials') }}</label>
              </div>
            @endif

            <div class="text-end mt-4"><button class="btn btn-primary" type="submit">{{ __('admin_messaging.save_provider') }}</button></div>
          </form>
        </div>
      </div>
    </div>
  @endforeach
</div>
