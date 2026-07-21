<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function change(Request $request) {
        $lang = $request->lang;
        abort_unless(array_key_exists($lang, config('supported_locales')), 404);
        Session::put("locale", $lang);
        return redirect()->back();
    }
}
