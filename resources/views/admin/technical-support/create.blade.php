@section('title', __('admin_support.title'))

<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item active">{{ __('admin_support.title') }}</li></ol></nav>

    <div class="row justify-content-center">
      <div class="col-12 col-xl-9 col-xxl-8">
        <div class="d-flex align-items-start gap-3 mb-4">
          <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:3rem;height:3rem"><span data-feather="life-buoy"></span></div>
          <div><h2 class="mb-1">{{ __('admin_support.title') }}</h2><p class="text-body-tertiary mb-0">{{ __('admin_support.subtitle') }}</p></div>
        </div>

        @if(session('support_success'))<div class="alert alert-subtle-success d-flex align-items-center" role="alert"><span data-feather="check-circle" class="me-2"></span>{{ session('support_success') }}</div>@endif
        @if(session('support_error'))<div class="alert alert-subtle-danger" role="alert">{{ session('support_error') }}</div>@endif
        @if($errors->has('support'))<div class="alert alert-subtle-danger" role="alert">{{ $errors->first('support') }} @can('settings.update')<a class="alert-link" href="{{ route('settings.index', ['tab'=>'site']) }}">{{ __('admin_support.open_settings') }}</a>@endcan</div>@endif

        <div class="card shadow-none border">
          <div class="card-body p-4 p-lg-5">
            <form method="POST" action="{{ route('technical-support.store') }}">
              @csrf
              <input type="hidden" name="page_url" value="{{ old('page_url', $pageUrl) }}">

              <div class="row g-4">
                <div class="col-12 col-md-5">
                  <label class="form-label" for="support_category">{{ __('admin_support.category') }}</label>
                  <select class="form-select @error('category') is-invalid @enderror" id="support_category" name="category" required>
                    @foreach(['question','problem','idea'] as $category)<option value="{{ $category }}" @selected(old('category','problem')===$category)>{{ __('admin_support.categories.'.$category) }}</option>@endforeach
                  </select>
                  @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-7">
                  <label class="form-label" for="support_subject">{{ __('admin_support.subject') }}</label>
                  <input class="form-control @error('subject') is-invalid @enderror" id="support_subject" name="subject" type="text" maxlength="150" value="{{ old('subject') }}" placeholder="{{ __('admin_support.subject_placeholder') }}" required>
                  @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                  <label class="form-label" for="support_message">{{ __('admin_support.message') }}</label>
                  <textarea class="form-control @error('message') is-invalid @enderror" id="support_message" name="message" rows="8" minlength="10" maxlength="5000" placeholder="{{ __('admin_support.message_placeholder') }}" required>{{ old('message') }}</textarea>
                  <div class="d-flex justify-content-between mt-1"><small class="text-body-tertiary">{{ __('admin_support.message_hint') }}</small><small id="supportMessageCounter" class="text-body-tertiary">0 / 5000</small></div>
                  @error('message')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="rounded-3 bg-body-highlight border p-3 mt-4">
                <div class="d-flex align-items-start gap-2"><span data-feather="info" class="text-primary flex-shrink-0 mt-1"></span><small class="text-body-secondary">{{ __('admin_support.sender_note', ['email'=>auth()->user()->email]) }}</small></div>
                @if(old('page_url', $pageUrl))<div class="d-flex align-items-start gap-2 mt-2"><span data-feather="link" class="text-body-tertiary flex-shrink-0 mt-1"></span><small class="text-body-tertiary text-break">{{ __('admin_support.page_attached') }}: {{ old('page_url', $pageUrl) }}</small></div>@endif
              </div>

              <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">
                <a class="btn btn-phoenix-secondary" href="{{ url()->previous() }}">{{ __('admin_support.cancel') }}</a>
                <button class="btn btn-primary" type="submit"><span data-feather="send" class="me-2"></span>{{ __('admin_support.send') }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <x-dashboard-footer />
  </div>

  @push('scripts')
  <script>
    (() => { const field=document.getElementById('support_message'),counter=document.getElementById('supportMessageCounter'); if(!field||!counter)return; const update=()=>counter.textContent=`${field.value.length} / 5000`; field.addEventListener('input',update); update(); })();
  </script>
  @endpush
</x-dashboard-layout>
