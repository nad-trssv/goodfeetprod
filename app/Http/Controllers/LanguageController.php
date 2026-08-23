<?php

namespace App\Http\Controllers;

use App\Services\Localization\SiteLocaleRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function change(Request $request, SiteLocaleRegistry $locales) {
        $lang = (string) $request->route('lang');
        abort_unless(array_key_exists($lang, $locales->activeLabels()), 404);
        Session::put('locale', $lang);

        $customer = $request->user('customer');
        if ($customer && $customer->locale !== $lang) {
            $customer->forceFill(['locale' => $lang])->save();
        }

        return redirect()->back();
    }
}
