@section('title', __('feedback.title'))
<x-app-layout>
  <div class="container-small py-7 py-lg-9">
    <div class="card border-0 shadow-sm mx-auto" style="max-width:42rem">
      <div class="card-body p-4 p-md-6 text-center">
        @if(session('feedback_saved') || $feedback->submitted_at)
          <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success mb-3" style="width:4rem;height:4rem"><span data-feather="check" style="width:2rem;height:2rem"></span></span>
          <h1 class="h3 mb-2">{{ __('feedback.thank_you') }}</h1>
          <p class="text-body-secondary mb-0">{{ __('feedback.saved') }}</p>
        @else
          <h1 class="h3 mb-2">{{ __('feedback.title') }}</h1>
          <p class="text-body-secondary mb-4">{{ __('feedback.question', ['service' => $feedback->appointment->service?->getTranslation(app()->getLocale(), 'name') ?: $feedback->appointment->service?->name]) }}</p>
          <form method="POST" action="{{ route('appointment-feedback.store', $feedback) }}">
            @csrf
            <fieldset class="border-0 p-0 m-0">
              <legend class="visually-hidden">{{ __('feedback.choose_rating') }}</legend>
              <div class="d-flex flex-wrap justify-content-center gap-2 mb-4" dir="ltr">
                @foreach(range(1, 5) as $rating)
                  <input class="btn-check" id="rating-{{ $rating }}" name="rating" type="radio" value="{{ $rating }}" required @checked(old('rating') == $rating)>
                  <label class="btn btn-outline-warning px-3 py-2" for="rating-{{ $rating }}" aria-label="{{ __('feedback.stars', ['count'=>$rating]) }}"><span class="fas fa-star me-1"></span>{{ $rating }}</label>
                @endforeach
              </div>
            </fieldset>
            @error('rating')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror
            <button class="btn btn-primary px-5" type="submit">{{ __('feedback.submit') }}</button>
          </form>
        @endif
      </div>
    </div>
  </div>
</x-app-layout>
