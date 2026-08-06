@section('title', 'Сотрудники')
@push('styles')
<style>
  .team-summary{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:.75rem}.team-summary__item{border:1px solid var(--phoenix-border-color);border-radius:14px;background:var(--phoenix-body-bg);padding:1rem}.team-summary__value{font-size:1.45rem;font-weight:750;line-height:1}.member-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,340px),1fr));gap:1rem}.member-card{position:relative;min-width:0;border:1px solid var(--phoenix-border-color);border-radius:16px;background:var(--phoenix-body-bg);padding:1.1rem;transition:transform .15s ease,box-shadow .15s ease,border-color .15s ease}.member-card:hover{transform:translateY(-2px);box-shadow:0 .5rem 1.5rem rgba(15,23,42,.08);border-color:var(--phoenix-primary)}.member-card__avatar,.member-card__avatar-fallback{width:58px;height:58px;flex:0 0 58px}.member-card__avatar{object-fit:cover}.member-card__avatar-fallback{display:flex;align-items:center;justify-content:center;border-radius:50%;background:var(--phoenix-primary-bg-subtle);color:var(--phoenix-primary);font-size:1.25rem;font-weight:750}.member-card__name{overflow-wrap:anywhere}.member-state{display:inline-flex;align-items:center;gap:.4rem;border-radius:999px;padding:.32rem .58rem;font-size:.72rem;font-weight:700}.member-state:before{content:"";width:7px;height:7px;border-radius:50%;background:currentColor}.member-state--available{color:#13795b;background:rgba(25,135,84,.12)}.member-state--busy{color:#b54708;background:rgba(253,126,20,.13)}.member-state--break{color:#7c3aed;background:rgba(124,58,237,.12)}.member-state--not_working{color:#667085;background:rgba(102,112,133,.12)}.member-state--vacation{color:#087990;background:rgba(13,202,240,.12)}.online-dot{position:absolute;right:0;bottom:1px;width:13px;height:13px;border:2px solid var(--phoenix-body-bg);border-radius:50%;background:#20c997}.member-meta{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.65rem}.member-meta__item{min-width:0;border-radius:10px;background:var(--phoenix-emphasis-bg);padding:.65rem}.member-meta__item small,.appointment-panel small{display:block;color:var(--phoenix-tertiary-color);font-size:.7rem;text-transform:uppercase;letter-spacing:.03em}.member-meta__item strong{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.82rem}.member-outcomes{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));overflow:hidden;border:1px solid var(--phoenix-border-color);border-radius:10px}.member-outcomes>div{min-width:0;padding:.5rem .25rem;text-align:center;border-right:1px solid var(--phoenix-border-color)}.member-outcomes>div:last-child{border-right:0}.member-outcomes strong{display:block;font-size:.78rem;white-space:nowrap}.member-outcomes small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.58rem;color:var(--phoenix-tertiary-color)}.appointment-panel{border-left:3px solid var(--phoenix-primary);border-radius:8px;background:var(--phoenix-primary-bg-subtle);padding:.65rem .75rem;min-width:0}.appointment-panel--action{border-left-color:#dc3545;background:rgba(220,53,69,.07)}.appointment-panel strong,.appointment-panel span{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.filter-btn.active{color:#fff;background:var(--phoenix-primary);border-color:var(--phoenix-primary)}@media(max-width:991.98px){.team-summary{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:575.98px){.team-summary{grid-template-columns:repeat(2,minmax(0,1fr))}.member-card{padding:.9rem}.member-meta{grid-template-columns:1fr}.team-toolbar>*{width:100%}.team-toolbar .btn-group{overflow:auto;flex-wrap:nowrap}.team-toolbar .btn{white-space:nowrap}}
</style>
@endpush

<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item active">Сотрудники</li></ol></nav>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div><h2 class="mb-1">Команда</h2><p class="text-body-tertiary mb-0">Кто сейчас работает, занят или доступен для записи.</p></div>
      <a href="{{ route('member.create') }}" class="btn btn-primary"><span class="fas fa-plus me-2"></span>Добавить сотрудника</a>
    </div>

    <div class="team-summary mb-4">
      <div class="team-summary__item"><div class="team-summary__value">{{ $summary['total'] }}</div><small class="text-body-tertiary">Всего</small></div>
      <div class="team-summary__item"><div class="team-summary__value text-success">{{ $summary['online'] }}</div><small class="text-body-tertiary">Онлайн</small></div>
      <div class="team-summary__item"><div class="team-summary__value text-success">{{ $summary['available'] }}</div><small class="text-body-tertiary">Свободны сейчас</small></div>
      <div class="team-summary__item"><div class="team-summary__value text-warning">{{ $summary['busy'] }}</div><small class="text-body-tertiary">С клиентом</small></div>
      <div class="team-summary__item"><div class="team-summary__value text-body-tertiary">{{ $summary['not_working'] }}</div><small class="text-body-tertiary">Не работают</small></div>
    </div>

    <div class="team-toolbar d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div class="search-box flex-grow-1" style="max-width:420px"><form class="position-relative" onsubmit="return false"><input id="memberSearch" class="form-control search-input" type="search" placeholder="Поиск по имени, телефону или email"><span class="fas fa-search search-box-icon"></span></form></div>
      <div class="btn-group" role="group" aria-label="Фильтр сотрудников">
        <button class="btn btn-outline-secondary filter-btn active" data-filter="all">Все</button>
        <button class="btn btn-outline-secondary filter-btn" data-filter="online">Онлайн</button>
        <button class="btn btn-outline-secondary filter-btn" data-filter="available">Свободны</button>
        <button class="btn btn-outline-secondary filter-btn" data-filter="busy">Заняты</button>
        <button class="btn btn-outline-secondary filter-btn" data-filter="not_working">Не работают</button>
      </div>
    </div>

    <div id="memberGrid" class="member-grid">
      @foreach($members as $user)
        @php($state = $user['work_state'])
        <article class="member-card" data-member-card data-state="{{ $state['code'] }}" data-online="{{ $user['is_online'] ? '1' : '0' }}" data-search="{{ mb_strtolower($user['name'].' '.$user['email'].' '.$user['phone'].' '.$user['username']) }}">
          <div class="d-flex align-items-start gap-3 mb-3">
            <a class="position-relative flex-shrink-0" href="{{ route('member.edit',$user['id']) }}">
              <x-ui.avatar :user="$user" :size="58" :online="$user['is_online']" />
            </a>
            <div class="min-w-0 flex-grow-1">
              <div class="d-flex align-items-start justify-content-between gap-2">
                <div class="min-w-0"><a class="text-body-emphasis text-decoration-none" href="{{ route('member.edit',$user['id']) }}"><h5 class="member-card__name mb-1">{{ $user['name'] }} @if($user['vacation'])🌴@endif</h5></a><div class="text-body-tertiary fs-10">{{ $user['role_id'] === 1 ? 'Администратор' : 'Мастер' }} · {{ $user['username'] }}</div></div>
                <div class="dropdown"><button class="btn btn-sm btn-subtle-secondary dropdown-toggle dropdown-caret-none" data-bs-toggle="dropdown" aria-label="Действия"><span class="fas fa-ellipsis-h"></span></button><div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="{{ route('member.edit',$user['id']) }}">Карточка сотрудника</a><a class="dropdown-item" href="{{ route('master.calendar',$user['id']) }}">Календарь</a><a class="dropdown-item" href="{{ route('master.calendar.list',$user['id']) }}">Записи</a><div class="dropdown-divider"></div><button class="dropdown-item text-danger member-destroy" type="button" data-id="{{ $user['id'] }}" data-item="{{ $user['name'] }}" data-bs-toggle="modal" data-bs-target="#deleteModal">Удалить</button></div></div>
              </div>
              <div class="mt-2"><span class="member-state member-state--{{ $state['code'] }}">{{ $state['label'] }}</span></div>
            </div>
          </div>

          <div class="member-meta mb-3">
            <div class="member-meta__item"><small>Сегодня</small><strong>{{ $user['today_hours'] ?: 'Выходной' }}</strong></div>
            <div class="member-meta__item"><small>Активность</small><strong>{{ $user['is_online'] ? 'Сейчас онлайн' : $user['last_seen'] }}</strong></div>
            <div class="member-meta__item"><small>Услуги</small><strong>{{ $user['services_count'] }} активных</strong></div>
            <div class="member-meta__item"><small>Статус</small><strong title="{{ $state['detail'] }}">{{ $state['detail'] ?: '—' }}</strong></div>
          </div>

          <div class="member-outcomes mb-3">
            <div title="Завершено"><strong class="text-success">{{ $user['today_stats']['completed'] }}</strong><small>готово</small></div>
            <div title="Выручка"><strong class="text-success">{{ number_format($user['today_stats']['earned'],0,',',' ') }} €</strong><small>выручка</small></div>
            <div title="Клиенты не пришли"><strong class="text-danger">{{ $user['today_stats']['no_show'] }}</strong><small>неявки</small></div>
            <div title="Потеряно из-за неявок"><strong class="text-danger">{{ number_format($user['today_stats']['lost'],0,',',' ') }} €</strong><small>потеряно</small></div>
            <div title="Отменено"><strong>{{ $user['today_stats']['cancelled'] }}</strong><small>отменено</small></div>
          </div>

          @if($user['action_required'] && (auth()->user()->role_id === 1 || auth()->id() === $user['id']))
            <div class="appointment-panel appointment-panel--action mb-3" data-status-panel>
              <small>Нужно указать результат · {{ $user['action_required']['start'] }}</small><strong>{{ $user['action_required']['service'] }}</strong><span class="fs-10 text-body-secondary mb-2">{{ $user['action_required']['client'] }}</span>
              <div class="d-flex gap-2"><button class="btn btn-sm btn-success flex-grow-1 appointment-status" data-id="{{ $user['action_required']['id'] }}" data-status="completed">Завершено</button><button class="btn btn-sm btn-outline-danger flex-grow-1 appointment-status" data-id="{{ $user['action_required']['id'] }}" data-status="no_show">Не пришёл</button></div>
            </div>
          @elseif($user['current_appointment'])
            <div class="appointment-panel mb-3"><small>Текущая запись · до {{ $user['current_appointment']['end'] }}</small><strong>{{ $user['current_appointment']['service'] }}</strong><span class="fs-10 text-body-secondary">{{ $user['current_appointment']['client'] }}</span></div>
          @elseif($user['next_appointment'])
            <div class="appointment-panel mb-3"><small>Ближайшая запись · {{ $user['next_appointment']['start'] }}</small><strong>{{ $user['next_appointment']['service'] }}</strong><span class="fs-10 text-body-secondary">{{ $user['next_appointment']['client'] }}</span></div>
          @else
            <div class="appointment-panel mb-3"><small>Ближайшая запись</small><strong>Нет предстоящих записей</strong></div>
          @endif

          <div class="d-flex flex-wrap gap-2"><a class="btn btn-sm btn-primary flex-grow-1" href="{{ route('member.edit',$user['id']) }}">Открыть карточку</a><a class="btn btn-sm btn-outline-secondary" href="{{ route('master.calendar.list',$user['id']) }}" title="Записи сотрудника"><span class="fas fa-list"></span></a><a class="btn btn-sm btn-outline-secondary" href="{{ route('master.calendar',$user['id']) }}" title="Календарь"><span class="fas fa-calendar-alt"></span></a><a class="btn btn-sm btn-outline-secondary" href="tel:{{ $user['phone'] }}" title="Позвонить"><span class="fas fa-phone"></span></a></div>
        </article>
      @endforeach
    </div>
    <div id="membersEmpty" class="text-center py-6 d-none"><span class="fas fa-user-slash fs-3 text-body-tertiary mb-3"></span><h5>Сотрудники не найдены</h5><p class="text-body-tertiary">Измените поиск или выбранный фильтр.</p></div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Удалить сотрудника?</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body">Будет удалён сотрудник «<strong id="deleteItemName"></strong>». Это действие нельзя отменить.</div><div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Отмена</button><button class="btn btn-danger" type="button" id="confirmDelete">Удалить</button></div></div></div></div>
    <x-dashboard-footer />
  </div>

  @push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded',()=>{let filter='all',deleteId=null;const token=document.querySelector('meta[name="csrf-token"]').content,cards=[...document.querySelectorAll('[data-member-card]')],search=document.getElementById('memberSearch'),empty=document.getElementById('membersEmpty');const apply=()=>{const term=search.value.trim().toLocaleLowerCase();let visible=0;cards.forEach(card=>{const state=card.dataset.state;const matchesFilter=filter==='all'||(filter==='online'&&card.dataset.online==='1')||(filter==='not_working'&&['not_working','vacation'].includes(state))||state===filter;const show=matchesFilter&&card.dataset.search.includes(term);card.classList.toggle('d-none',!show);if(show)visible++});empty.classList.toggle('d-none',visible>0)};search.addEventListener('input',apply);document.querySelectorAll('.filter-btn').forEach(button=>button.addEventListener('click',()=>{filter=button.dataset.filter;document.querySelectorAll('.filter-btn').forEach(item=>item.classList.toggle('active',item===button));apply()}));document.addEventListener('click',async event=>{const destroy=event.target.closest('.member-destroy');if(destroy){deleteId=destroy.dataset.id;document.getElementById('deleteItemName').textContent=destroy.dataset.item;return}const statusButton=event.target.closest('.appointment-status');if(!statusButton)return;statusButton.disabled=true;const response=await fetch(`/appointments/${statusButton.dataset.id}/status`,{method:'PATCH',headers:{'X-CSRF-TOKEN':token,'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify({status:statusButton.dataset.status})});if(response.ok){location.reload();return}statusButton.disabled=false;const data=await response.json();alert(data.message||Object.values(data.errors||{}).flat()[0]||'Не удалось изменить статус записи.')} );document.getElementById('confirmDelete').addEventListener('click',async()=>{if(!deleteId)return;const response=await fetch(`/member/${deleteId}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'}});if(response.ok)location.reload()})});
  </script>
  @endpush
</x-dashboard-layout>
