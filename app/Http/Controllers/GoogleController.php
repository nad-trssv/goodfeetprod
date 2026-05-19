<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function google_redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function google_callback()
    {
        $googleUser = Socialite::driver('google')->user();
    }
}
