<?php

namespace App\Http\Controllers;

use App\Http\Requests\TechnicalSupportRequest;
use App\Services\TechnicalSupportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class TechnicalSupportController extends Controller
{
    public function create(Request $request): View
    {
        $previous = url()->previous();
        $pageUrl = parse_url($previous, PHP_URL_HOST) === $request->getHost() ? $previous : null;

        return view('admin.technical-support.create', compact('pageUrl'));
    }

    public function store(TechnicalSupportRequest $request, TechnicalSupportService $service): RedirectResponse
    {
        try {
            $service->send($request->user(), $request->validated());
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('support_error', __('admin_support.send_failed'));
        }

        return redirect()->route('technical-support.create')->with('support_success', __('admin_support.sent'));
    }
}
