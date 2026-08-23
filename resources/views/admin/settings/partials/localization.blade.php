<div class="row g-4 mb-4">
  <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-body-tertiary fs-9">{{ __('admin_localization.installed_count') }}</div><div class="fs-4 fw-bold">{{ $siteLocales->count() }}</div></div></div></div>
  <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-body-tertiary fs-9">{{ __('admin_localization.active_count') }}</div><div class="fs-4 fw-bold">{{ $siteLocales->where('enabled', true)->count() }}</div></div></div></div>
  <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-body-tertiary fs-9">{{ __('admin_localization.default_language') }}</div><div class="fs-6 fw-bold">{{ $siteLocales->firstWhere('code', $defaultSiteLocale)?->native_name ?? strtoupper($defaultSiteLocale) }}</div></div></div></div>
  <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body d-flex align-items-center"><a class="btn btn-primary w-100" href="{{ route('settings.localization.translations.index') }}"><span class="fas fa-language me-2"></span>{{ __('admin_localization.translate_frontend') }}</a></div></div></div>
</div>

<div class="card mb-4">
  <div class="card-header"><h5 class="mb-0">{{ __('admin_localization.site_languages') }}</h5></div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead><tr><th>{{ __('admin_localization.language') }}</th><th>{{ __('admin_localization.status') }}</th><th>{{ __('admin_localization.frontend_progress') }}</th><th>{{ __('admin_localization.services_progress') }}</th><th class="text-end">{{ __('admin_localization.actions') }}</th></tr></thead>
        <tbody>
          @foreach($siteLocales as $locale)
            @php($stats = $siteLocaleStatistics[$locale->code])
            <tr>
              <td><div class="fw-semibold">{{ $locale->native_name }} <span class="text-body-tertiary">({{ strtoupper($locale->code) }})</span></div><small>{{ $locale->name }}</small></td>
              <td>@if($locale->is_default)<span class="badge badge-phoenix badge-phoenix-primary">{{ __('admin_localization.default') }}</span>@endif <span class="badge badge-phoenix badge-phoenix-{{ $locale->enabled ? 'success' : 'secondary' }}">{{ $locale->enabled ? __('admin_localization.enabled') : __('admin_localization.disabled') }}</span></td>
              <td><span class="fw-semibold">{{ $stats['translatedStrings'] }}/{{ $stats['totalStrings'] }}</span><div class="text-body-tertiary fs-10">{{ __('admin_localization.manual_translations') }}</div></td>
              <td><span class="fw-semibold">{{ $stats['serviceTranslated'] }}/{{ $stats['serviceTotal'] }}</span><div class="text-body-tertiary fs-10">{{ __('admin_localization.services_ready') }}</div></td>
              <td><div class="d-flex flex-wrap justify-content-end gap-2">
                <form method="POST" action="{{ route('settings.localization.languages.update', $locale) }}">@csrf @method('PUT')<input type="hidden" name="enabled" value="{{ $locale->enabled ? 0 : 1 }}"><button class="btn btn-sm btn-outline-{{ $locale->enabled ? 'secondary' : 'success' }}" type="submit" @disabled($locale->is_default && $locale->enabled)>{{ $locale->enabled ? __('admin_localization.disable') : __('admin_localization.enable') }}</button></form>
                @unless($locale->is_default)<form method="POST" action="{{ route('settings.localization.languages.default', $locale) }}">@csrf @method('PUT')<button class="btn btn-sm btn-outline-primary" type="submit">{{ __('admin_localization.make_default') }}</button></form>@endunless
                @unless($locale->is_default)<a class="btn btn-sm btn-primary" href="{{ route('settings.localization.translations.index', ['locale' => $locale->code]) }}">{{ __('admin_localization.translate') }}</a>@endunless
              </div></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header"><h5 class="mb-0">{{ __('admin_localization.add_language') }}</h5></div>
  <div class="card-body">
    <p class="text-body-tertiary">{{ __('admin_localization.add_language_hint') }}</p>
    @if($availableSiteLocales)
      <form class="row g-3 align-items-end" method="POST" action="{{ route('settings.localization.languages.store') }}">@csrf
        <div class="col-md-8"><label class="form-label" for="site-locale-code">{{ __('admin_localization.available_language') }}</label><select class="form-select" id="site-locale-code" name="code" required><option value="">{{ __('admin_localization.select_language') }}</option>@foreach($availableSiteLocales as $code => $language)<option value="{{ $code }}">{{ $language['native_name'] }} — {{ $language['name'] }} ({{ strtoupper($code) }})</option>@endforeach</select></div>
        <div class="col-md-4"><button class="btn btn-primary w-100" type="submit">{{ __('admin_localization.add_and_copy') }}</button></div>
      </form>
    @else
      <div class="alert alert-subtle-info mb-0">{{ __('admin_localization.no_available_languages') }}</div>
    @endif
  </div>
</div>
