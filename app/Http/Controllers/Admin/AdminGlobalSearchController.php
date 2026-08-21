<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminGlobalSearchRequest;
use App\Services\AdminGlobalSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AdminGlobalSearchController extends Controller
{
    public function __invoke(AdminGlobalSearchRequest $request, AdminGlobalSearch $search): View|JsonResponse
    {
        $term = trim((string) $request->validated('q', ''));
        $results = $search->search($request->user(), $term, $request->expectsJson() ? 5 : 20);

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('admin.search._groups', [
                    'results' => $results,
                    'term' => $term,
                    'compact' => true,
                ])->render(),
                'results_url' => route('admin.search', ['q' => $term]),
            ]);
        }

        return view('admin.search.index', compact('results', 'term'));
    }
}
