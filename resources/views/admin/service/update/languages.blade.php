@section('title', 'Services')
  @push('styles')
    <link href="{{ asset('vendors/choices/choices.min.css') }}" rel="stylesheet" />
  @endpush
  <x-dashboard-layout>
    <div class="content">
      <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="{{ route('service.index') }}">Услуги</a></li>
          <li class="breadcrumb-item"><a href="{{ route('service.show', $service['id']) }}">Редактирование</a></li>
          <li class="breadcrumb-item active">Локализация</li>
        </ol>
      </nav>
      
      @if (session('success'))
        <script>
          document.addEventListener("DOMContentLoaded", function() {
              Swal.fire({
                  title: 'Обновлено!',
                  text: "{{ session('success') }}",
                  icon: 'success',
                  confirmButtonText: 'OK'
              });
          });
        </script>
      @endif

      <form action="{{ route('languages.update', $service['id']) }}" method="POST">
        @csrf
        @method('PUT') 
      <div class="row g-4">
        <div class="col-12 col-xl-10 order-1 order-xl-0">
            @foreach(['ru' => 'Русский язык', 'et' => 'Eesti keel', 'en' => 'English'] as $locale => $language)
                    <div class="card shadow-none border mb-4 mt-6" data-component-card="data-component-card">
                      <div class="card-header p-4 border-bottom bg-body">
                        <div class="row g-3 justify-content-between align-items-center">
                          <div class="col-12 col-md">
                            <h4 class="text-body mb-0" data-anchor="data-anchor" id="lang-{{ $locale }}">{{ $language }}<a class="anchorjs-link " aria-label="Anchor" data-anchorjs-icon="#" href="#lang-{{ $locale }}" style="margin-left: 0.1875em; padding-right: 0.1875em; padding-left: 0.1875em;"></a></h4>
                          </div>
                        </div>
                      </div>
                      <div class="card-body p-0">
                        <div class="p-4">
                          <div class="row">
                            <div class="col-12">
                                <div class="col-12 mb-3">
                                  <div class="form-floating">
                                    <input type="text" class="form-control" name="translations[{{ $locale }}][name]" placeholder="Название проекта" value="{{ $service->getTranslation($locale, 'name') }}" required>
                                    <label for="name">Название проекта</label>
                                    <div class="invalid-feedback">Это поле обязательное</div>
                                  </div>
                                </div>
                              
                                <div class="col-12 mb-3">
                                  <div class="form-floating">
                                    <textarea class="form-control" id="short_description" name="translations[{{ $locale }}][short_description]" placeholder="Краткое Описание" style="height: 100px">{{ $service->getTranslation($locale, 'short_description') }}</textarea>
                                    <label for="short_description">Краткое Описание</label>
                                  </div>
                                </div>
                                <div class="col-12 mb-3">
                                  <label for="full_description">Описание</label>
                                  <div class="form-floating">
                                    <textarea class="tinyarea" name="translations[{{ $locale }}][full_description]" data-tinymce="{}">{{ $service->getTranslation($locale, 'full_description') }}</textarea>
                                  </div>
                                </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
            @endforeach
            <div class="card shadow-none mb-4 mt-6">
                <div class="col-auto">
                  <button type="submit" class="btn btn-primary w-100 mt-2">Сохранить</button>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-2">
          <div class="position-sticky mt-xl-4 h-[100vh]" style="top: 80px;">
            <h5 class="lh-1">Поиск по странице</h5>
            <hr>
            <ul class="nav nav-vertical flex-column doc-nav" data-doc-nav="data-doc-nav">
              <li class="nav-item"> <a class="nav-link" href="#lang-ru">Русский язык</a>
              </li>
              <li class="nav-item"> <a class="nav-link" href="#lang-et">Eesti keel</a>
              </li>
              <li class="nav-item"> <a class="nav-link" href="#lang-en">English</a>
              </li>
            </ul>
            <hr>
            <ul class="nav nav-vertical flex-column doc-nav" data-doc-nav="data-doc-nav">
              <li class="nav-item">
                <button type="submit" class="btn btn-primary w-100 my-2 py-1">Сохранить изменения</button>
              </li>
              <li class="nav-item"> 
                <a class="btn border border-secondary text-secondary w-100 my-2 py-1" href="{{ route('service.show', $service['id']) }}">Вернуться в редактирование</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </form>
      <x-dashboard-footer />
    </div>
    @push('scripts')
      <script src="{{ asset('vendors/choices/choices.min.js') }}"></script>
      <script src="{{ asset('assets/js/tiny.js') }}"></script>
    @endpush
  </x-dashboard-layout>
