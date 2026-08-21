<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CrmRatingController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->hasAllAppointmentsScope() && $request->user()->hasPermission('crm.settings'),403);

        $staff = User::query()
            ->with('role')
            ->withCount([
                'crmRatings',
                'crmRatings as crm_ratings_1_count'=>fn($query)=>$query->where('rating',1),
                'crmRatings as crm_ratings_2_count'=>fn($query)=>$query->where('rating',2),
                'crmRatings as crm_ratings_3_count'=>fn($query)=>$query->where('rating',3),
                'crmRatings as crm_ratings_4_count'=>fn($query)=>$query->where('rating',4),
                'crmRatings as crm_ratings_5_count'=>fn($query)=>$query->where('rating',5),
            ])
            ->withAvg('crmRatings as crm_rating_average','rating')
            ->get()
            ->filter->isStaff()
            ->sortByDesc(fn($member)=>[$member->crm_ratings_count,$member->crm_rating_average])
            ->values();

        return view('admin.crm.ratings.index',compact('staff'));
    }
}
