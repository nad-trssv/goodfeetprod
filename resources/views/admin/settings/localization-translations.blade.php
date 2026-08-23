@section('title', __('admin_localization.translation_editor'))
<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('settings.index') }}#tab-localization">{{ __('admin_settings.title') }}</a></li><li class="breadcrumb-item active">{{ __('admin_localization.translation_editor') }}</li></ol></nav>
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4"><div><h2 class="mb-1">{{ __('admin_localization.translation_editor') }}</h2><p class="text-body-tertiary mb-0">{{ __('admin_localization.translation_editor_hint', ['language' => $installed->firstWhere('code', $default)?->native_name ?? strtoupper($default)]) }}</p></div><a class="btn btn-outline-secondary" href="{{ route('settings.index') }}#tab-localization">{{ __('admin_localization.back_to_languages') }}</a></div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card mb-4"><div class="card-body"><form class="row g-3 align-items-end" method="GET">
      <div class="col-md-4"><label class="form-label" for="translation-locale">{{ __('admin_localization.target_language') }}</label><select class="form-select" id="translation-locale" name="locale" required>@foreach($targets as $locale)<option value="{{ $locale->code }}" @selected($target === $locale->code)>{{ $locale->native_name }} ({{ strtoupper($locale->code) }})</option>@endforeach</select></div>
      <div class="col-md-6"><label class="form-label" for="translation-search">{{ __('admin_localization.search') }}</label><input class="form-control" id="translation-search" type="search" name="search" value="{{ $search }}" placeholder="{{ __('admin_localization.search_placeholder') }}"></div>
      <input type="hidden" name="status" value="{{ $status }}">
      <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">{{ __('admin_localization.find') }}</button></div>
      <div class="col-12"><div class="d-flex flex-wrap gap-2" role="group" aria-label="{{ __('admin_localization.filter_status') }}">
        @foreach(['all', 'untranslated', 'translated'] as $filter)
          <a class="btn btn-sm {{ $status === $filter ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('settings.localization.translations.index', array_filter(['locale' => $target, 'search' => $search, 'status' => $filter])) }}">{{ __('admin_localization.show_'.$filter) }}</a>
        @endforeach
      </div></div>
    </form></div></div>

    @if($targets->isEmpty())
      <div class="alert alert-subtle-info">{{ __('admin_localization.add_target_first') }}</div>
    @else
      <div class="d-grid gap-3">
        @forelse($rows as $row)
          <article class="card"><div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3"><code class="text-break">{{ $row['key'] }}</code>@if($row['inherited'])<span class="badge badge-phoenix badge-phoenix-warning align-self-start">{{ __('admin_localization.inherited') }}</span>@else<span class="badge badge-phoenix badge-phoenix-success align-self-start">{{ __('admin_localization.translated') }}</span>@endif</div>
            <div class="row g-3">
              <div class="col-lg-6"><label class="form-label">{{ __('admin_localization.source_text') }} · {{ strtoupper($default) }}</label><div class="form-control bg-body-highlight h-auto" style="min-height:5rem;white-space:pre-wrap">{{ $row['source'] }}</div></div>
              <div class="col-lg-6"><form method="POST" action="{{ route('settings.localization.translations.update', $target) }}">@csrf @method('PUT')<input type="hidden" name="translation_key" value="{{ $row['key'] }}"><label class="form-label">{{ __('admin_localization.translation') }} · {{ strtoupper($target) }}</label><textarea class="form-control" name="value" rows="3" maxlength="10000" required>{{ $row['target'] }}</textarea><div class="d-flex justify-content-between align-items-center gap-3 mt-2"><small class="text-body-tertiary">{{ __('admin_localization.placeholder_hint') }}</small><button class="btn btn-sm btn-primary flex-shrink-0" type="submit">{{ __('admin_localization.save_translation') }}</button></div></form></div>
            </div>
          </div></article>
        @empty
          <div class="alert alert-subtle-warning">{{ __('admin_localization.nothing_found') }}</div>
        @endforelse
      </div>
      <div class="mt-4">{{ $rows->links() }}</div>
    @endif
    <x-dashboard-footer />
  </div>
</x-dashboard-layout>
