<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromoCodeRequest;
use App\Models\PromoCode;
use App\Models\Services;

class PromoCodeController extends Controller
{
    public function index()
    {
        return view('admin.promo-codes.index', [
            'promoCodes' => PromoCode::withCount('redemptions')->latest()->paginate(20),
            'services' => Services::where('is_deleted', 0)->orderBy('name')->get(),
        ]);
    }

    public function store(PromoCodeRequest $request)
    {
        $promo = PromoCode::create($request->safe()->except('services'));
        $promo->services()->sync($request->validated('services', []));
        return back()->with('success', __('promo.saved'));
    }

    public function update(PromoCodeRequest $request, PromoCode $promoCode)
    {
        $promoCode->update($request->safe()->except('services'));
        $promoCode->services()->sync($request->validated('services', []));
        return back()->with('success', __('promo.saved'));
    }

    public function destroy(PromoCode $promoCode)
    {
        $promoCode->update(['active' => false]);
        return back()->with('success', __('promo.deactivated'));
    }
}
