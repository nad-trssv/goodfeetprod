@section('title', __('crm.ratings'))
<x-dashboard-layout>
<div class="content">
  <div class="mb-4">
    <h2 class="mb-1">{{ __('crm.ratings') }}</h2>
    <p class="text-body-tertiary mb-0">{{ __('crm.ratings_hint') }}</p>
  </div>
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead><tr><th>{{ __('admin_nav.staff') }}</th><th>{{ __('crm.average_rating') }}</th><th>{{ __('crm.ratings_total') }}</th><th style="min-width:20rem">{{ __('crm.rating_distribution') }}</th></tr></thead>
        <tbody>
          @foreach($staff as $member)
            <tr>
              <td><div class="d-flex align-items-center gap-2"><x-ui.avatar :user="$member" :size="40" /><div><strong class="d-block">{{ $member->name }}</strong><small class="text-body-tertiary">{{ $member->professionalTitle() }}</small></div></div></td>
              <td>@if($member->crm_ratings_count)<span class="fw-bold fs-7">{{ number_format((float)$member->crm_rating_average,2) }}</span><span class="text-warning ms-1">★</span>@else<span class="text-body-tertiary">—</span>@endif</td>
              <td>{{ $member->crm_ratings_count }}</td>
              <td>
                <div class="d-flex flex-wrap gap-2">
                  @foreach([5,4,3,2,1] as $score)
                    <span class="badge badge-phoenix badge-phoenix-secondary">{{ $score }} ★ · {{ $member->{'crm_ratings_'.$score.'_count'} }}</span>
                  @endforeach
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  <x-dashboard-footer />
</div>
</x-dashboard-layout>
