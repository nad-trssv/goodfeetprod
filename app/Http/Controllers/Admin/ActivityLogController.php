<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLogEntry;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim(
            (string) $request->query('search', '')
        );

        $module = trim(
            (string) $request->query('module', '')
        );

        $actorId = $request->integer('actor_id');

        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $logs = ActivityLogEntry::query()
            ->with('actor')

            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('message', 'like', '%' . $search . '%')
                        ->orWhere(
                            'actor_name',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'subject_name',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'event',
                            'like',
                            '%' . $search . '%'
                        );

                    if (ctype_digit($search)) {
                        $innerQuery->orWhere(
                            'subject_id',
                            (int) $search
                        );
                    }
                });
            })

            ->when($module !== '', function ($query) use ($module) {
                $query->where('module', $module);
            })

            ->when($actorId > 0, function ($query) use ($actorId) {
                $query->where('actor_id', $actorId);
            })

            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereDate(
                    'created_at',
                    '>=',
                    $dateFrom
                );
            })

            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate(
                    'created_at',
                    '<=',
                    $dateTo
                );
            })

            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        $modules = ActivityLogEntry::query()
            ->select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $actors = User::query()
            ->whereIn(
                'id',
                ActivityLogEntry::query()
                    ->whereNotNull('actor_id')
                    ->select('actor_id')
            )
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.activity-logs.index', [
            'logs' => $logs,
            'modules' => $modules,
            'actors' => $actors,
            'search' => $search,
            'module' => $module,
            'actorId' => $actorId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}