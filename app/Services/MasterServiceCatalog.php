<?php
namespace App\Services;
use App\Models\Services;
use App\Models\User;
use App\Models\UserServices;
use Illuminate\Database\Eloquent\Builder;
class MasterServiceCatalog
{
    public function get(User $master, string $search = '', string $filter = 'all'): array
    {
        $filter = in_array($filter, ['all', 'active'], true) ? $filter : 'all';
        $services = $this->query($master, trim($search), $filter)->get();
        $settings = UserServices::where('user_id', $master->id)->get()->keyBy('service_id');
        return compact('services', 'settings', 'filter');
    }
    private function query(User $master, string $search, string $filter): Builder
    {
        return Services::query()->where('status', true)->where('is_deleted', false)
            ->when($search !== '', fn (Builder $query) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($filter === 'active', fn (Builder $query) => $query->whereExists(fn ($subQuery) => $subQuery->selectRaw('1')->from('user_services')->whereColumn('user_services.service_id', 'services.id')->where('user_services.user_id', $master->id)->where('user_services.is_active', true)))
            ->orderBy('name');
    }
}
