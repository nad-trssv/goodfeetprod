<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteLocale;
use App\Services\Localization\FrontendTranslationCatalog;
use App\Services\Localization\SiteLocaleRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SiteLocalizationController extends Controller
{
    public function storeLanguage(
        Request $request,
        SiteLocaleRegistry $locales,
        FrontendTranslationCatalog $catalogue,
    ): RedirectResponse {
        $this->authorizeAccess($request);
        $data = $request->validate([
            'code' => ['required', 'string', Rule::in(array_keys($locales->available()))],
        ]);

        $locale = $locales->add($data['code'], $catalogue);

        return redirect()->to(route('settings.index').'#tab-localization')
            ->with('settings_tab', 'localization')
            ->with('success', __('admin_localization.language_added', ['language' => $locale->native_name]));
    }

    public function updateLanguage(Request $request, SiteLocale $locale, SiteLocaleRegistry $locales): RedirectResponse
    {
        $this->authorizeAccess($request);
        $data = $request->validate(['enabled' => ['required', 'boolean']]);
        $locales->update($locale, (bool) $data['enabled']);

        return redirect()->to(route('settings.index').'#tab-localization')
            ->with('settings_tab', 'localization')
            ->with('success', __('admin_localization.language_updated'));
    }

    public function makeDefault(Request $request, SiteLocale $locale, SiteLocaleRegistry $locales): RedirectResponse
    {
        $this->authorizeAccess($request);
        $locales->makeDefault($locale);

        return redirect()->to(route('settings.index').'#tab-localization')
            ->with('settings_tab', 'localization')
            ->with('success', __('admin_localization.default_updated'));
    }

    public function translations(
        Request $request,
        SiteLocaleRegistry $locales,
        FrontendTranslationCatalog $catalogue,
    ): View {
        $this->authorizeAccess($request);
        $installed = $locales->installed();
        $default = $locales->defaultCode();
        $targets = $installed->where('code', '!=', $default)->values();
        $target = (string) $request->query('locale', $targets->first()?->code ?? '');
        abort_if($target !== '' && ! $targets->contains('code', $target), 404);
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', 'all');
        abort_unless(in_array($status, ['all', 'untranslated', 'translated'], true), 422);
        $allRows = $target === '' ? collect() : $catalogue->rows($target, $search, $status);
        $page = max(1, $request->integer('page', 1));
        $perPage = 30;
        $rows = new LengthAwarePaginator(
            $allRows->forPage($page, $perPage)->values(),
            $allRows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return view('admin.settings.localization-translations', compact(
            'installed', 'default', 'targets', 'target', 'search', 'status', 'rows',
        ));
    }

    public function updateTranslation(
        Request $request,
        SiteLocale $locale,
        FrontendTranslationCatalog $catalogue,
        SiteLocaleRegistry $locales,
    ): RedirectResponse {
        $this->authorizeAccess($request);
        abort_if($locale->code === $locales->defaultCode(), 422);
        $data = $request->validate([
            'translation_key' => ['required', 'string', 'max:190'],
            'value' => ['required', 'string', 'max:10000'],
        ]);
        $catalogue->save($locale->code, $data['translation_key'], $data['value']);

        return back()->with('success', __('admin_localization.translation_saved'));
    }

    private function authorizeAccess(Request $request): void
    {
        abort_unless(
            $request->user()?->role?->resolvedSlug() === 'super-admin'
                && $request->user()->hasPermission('settings.update'),
            403,
        );
    }
}
