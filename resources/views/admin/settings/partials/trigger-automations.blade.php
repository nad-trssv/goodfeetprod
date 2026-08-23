<div class="card border mb-4">
  <div class="card-header">
    <h5 class="mb-1">{{ __('admin_messaging.automation_title') }}</h5>
    <p class="text-body-secondary fs-9 mb-0">{{ __('admin_messaging.automation_hint') }}</p>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('settings.messaging-automation.update') }}">
      @csrf
      @method('PUT')
      <input type="hidden" name="settings_tab" value="integrations">

      <div class="alert alert-subtle-info border fs-9">{{ __('admin_messaging.placeholders_hint') }} <code>{client_name}</code>, <code>{service}</code>, <code>{master}</code>, <code>{date}</code>, <code>{time}</code>, <code>{company}</code>, <code>{feedback_url}</code>.</div>

      @foreach(\App\Models\MessagingTriggerSetting::TRIGGERS as $trigger)
        @php
          $triggerSetting = $messagingTriggerSettings->get($trigger);
        @endphp
        <section class="border rounded-3 p-3 p-lg-4 mb-4">
          <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
            <div><h6 class="mb-1">{{ __('admin_messaging.triggers.'.$trigger.'.name') }}</h6><p class="text-body-secondary fs-9 mb-0">{{ __('admin_messaging.triggers.'.$trigger.'.hint') }}</p></div>
            <div class="form-check form-switch">
              <input type="hidden" name="triggers[{{ $trigger }}][enabled]" value="0">
              <input class="form-check-input" id="trigger-{{ $trigger }}" name="triggers[{{ $trigger }}][enabled]" type="checkbox" value="1" @checked(old("triggers.$trigger.enabled", $triggerSetting?->enabled))>
              <label class="form-check-label" for="trigger-{{ $trigger }}">{{ __('admin_messaging.trigger_enabled') }}</label>
            </div>
          </div>
          @if($trigger === 'booking_created')
            <input type="hidden" name="triggers[{{ $trigger }}][timing_minutes]" value="0">
          @else
            <div class="row align-items-end mb-3"><div class="col-12 col-sm-6 col-lg-4">
              <label class="form-label" for="timing-{{ $trigger }}">{{ __('admin_messaging.triggers.'.$trigger.'.timing') }}</label>
              <div class="input-group"><input class="form-control" id="timing-{{ $trigger }}" type="number" min="{{ $trigger === 'review_request' ? 0 : 60 }}" max="{{ $trigger === 'review_request' ? 720 : 10080 }}" name="triggers[{{ $trigger }}][timing_minutes]" value="{{ old("triggers.$trigger.timing_minutes", $triggerSetting?->timing_minutes) }}"><span class="input-group-text">{{ __('admin_messaging.minutes') }}</span></div>
            </div></div>
          @endif
          @php
            $localeCodes = array_keys($supportedLocales);
            $activeTemplateLocale = collect($localeCodes)->first(
              fn ($code) => $errors->has("triggers.$trigger.templates.$code")
            ) ?? ($localeCodes[0] ?? null);
            $filledTemplateCount = collect($localeCodes)->filter(
              fn ($code) => filled(old("triggers.$trigger.templates.$code", $triggerSetting?->templates[$code] ?? ''))
            )->count();
          @endphp
          <div class="trigger-language-editor" data-language-editor data-progress-template="{{ __('admin_messaging.language_progress', ['filled' => '{filled}', 'total' => '{total}']) }}">
            <div class="trigger-language-toolbar d-flex flex-column flex-sm-row align-items-sm-end justify-content-between gap-2 mb-3">
              <div class="trigger-language-select-wrap">
                <label class="form-label" for="template-language-{{ $trigger }}">{{ __('admin_messaging.message_language') }}</label>
                <select class="form-select" id="template-language-{{ $trigger }}" data-language-select>
                  @foreach($supportedLocales as $code => $label)
                    @php
                      $templateValue = old("triggers.$trigger.templates.$code", $triggerSetting?->templates[$code] ?? '');
                      $templateMarker = filled($templateValue) ? '✓' : '○';
                    @endphp
                    <option value="{{ $code }}" data-label="{{ $label }}" data-code="{{ strtoupper($code) }}" @if($activeTemplateLocale === $code) selected @endif>{{ $templateMarker }} {{ $label }} ({{ strtoupper($code) }})</option>
                  @endforeach
                </select>
              </div>
              <div class="d-flex align-items-center gap-2 pb-sm-2">
                <span class="badge badge-phoenix badge-phoenix-primary" data-current-language>{{ strtoupper((string) $activeTemplateLocale) }}</span>
                <span class="text-body-secondary fs-9" data-language-progress>{{ __('admin_messaging.language_progress', ['filled' => $filledTemplateCount, 'total' => count($supportedLocales)]) }}</span>
              </div>
            </div>
            <p class="text-body-secondary fs-9 mb-2">{{ __('admin_messaging.language_switch_hint') }}</p>
            @foreach($supportedLocales as $code => $label)
              <div data-language-pane="{{ $code }}" @if($activeTemplateLocale !== $code) hidden @endif>
                <label class="form-label" for="template-{{ $trigger }}-{{ $code }}">{{ $label }} ({{ strtoupper($code) }})</label>
                <textarea class="form-control @error("triggers.$trigger.templates.$code") is-invalid @enderror" id="template-{{ $trigger }}-{{ $code }}" name="triggers[{{ $trigger }}][templates][{{ $code }}]" data-language-textarea="{{ $code }}" rows="5" maxlength="2000" aria-required="true">{{ old("triggers.$trigger.templates.$code", $triggerSetting?->templates[$code] ?? '') }}</textarea>
                @error("triggers.$trigger.templates.$code")<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            @endforeach
          </div>
        </section>
      @endforeach

      <h6 class="mb-1">{{ __('admin_messaging.priority_title') }}</h6>
      <p class="text-body-secondary fs-9 mb-3">{{ __('admin_messaging.priority_hint') }}</p>
      @error('priorities')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror
      <div class="row g-3 mb-4">
        @foreach(config('messaging_integrations.providers') as $provider => $definition)
          @php
            $integration = $messagingIntegrations->get($provider);
            $configuredOrder = array_search($provider, config('messaging_integrations.provider_order'), true);
            $defaultPriority = $configuredOrder === false ? 10 : $configuredOrder + 1;
            $selectedPriority = (int) old("priorities.$provider", $integration?->priority ?: $defaultPriority);
          @endphp
          <div class="col-12 col-md-4"><label class="form-label" for="priority-{{ $provider }}">{{ __('admin_messaging.providers.'.$provider.'.name') }}</label><select class="form-select" id="priority-{{ $provider }}" name="priorities[{{ $provider }}]">@foreach(range(1, count(config('messaging_integrations.providers'))) as $position)<option value="{{ $position }}" @selected($selectedPriority === $position)>{{ $position }}</option>@endforeach</select></div>
        @endforeach
      </div>
      <div class="text-end"><button class="btn btn-primary" type="submit">{{ __('admin_messaging.save_automation') }}</button></div>
    </form>
  </div>
</div>

@once
  <style>
    .trigger-language-toolbar {
      border: 1px solid var(--phoenix-border-color);
      border-radius: .5rem;
      background: var(--phoenix-emphasis-bg);
      padding: .75rem 1rem;
    }
    .trigger-language-select-wrap {
      width: min(100%, 22rem);
    }
    [data-language-pane][hidden] {
      display: none !important;
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('[data-language-editor]').forEach(function (editor) {
        const select = editor.querySelector('[data-language-select]');
        const progress = editor.querySelector('[data-language-progress]');
        const currentLanguage = editor.querySelector('[data-current-language]');
        const panes = Array.from(editor.querySelectorAll('[data-language-pane]'));
        const textareas = Array.from(editor.querySelectorAll('[data-language-textarea]'));

        if (!select) return;

        const activate = function () {
          panes.forEach(function (pane) {
            pane.hidden = pane.dataset.languagePane !== select.value;
          });
          const selected = select.options[select.selectedIndex];
          if (currentLanguage && selected) currentLanguage.textContent = selected.dataset.code;
        };

        const refreshProgress = function () {
          let filled = 0;
          Array.from(select.options).forEach(function (option) {
            const textarea = textareas.find(function (item) {
              return item.dataset.languageTextarea === option.value;
            });
            const hasText = Boolean(textarea && textarea.value.trim());
            if (hasText) filled++;
            option.textContent = (hasText ? '✓ ' : '○ ') + option.dataset.label + ' (' + option.dataset.code + ')';
          });
          if (progress) {
            progress.textContent = editor.dataset.progressTemplate
              .replace('{filled}', String(filled))
              .replace('{total}', String(textareas.length));
          }
        };

        select.addEventListener('change', activate);
        textareas.forEach(function (textarea) {
          textarea.addEventListener('input', refreshProgress);
        });
        activate();
        refreshProgress();
      });
    });
  </script>
@endonce
