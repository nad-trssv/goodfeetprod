@section('title', __('admin_staff.closure_calendar_title',['name'=>$member->name]))
@push('styles')
<style>#closure-calendar{min-height:650px}.fc .fc-event{cursor:pointer;white-space:normal}.fc .fc-daygrid-day-number{padding:.45rem}.fc .fc-toolbar{flex-wrap:wrap;gap:.5rem}@media(max-width:575px){.fc .fc-toolbar{font-size:.85rem}.fc .fc-toolbar-title{font-size:1.15rem!important}}</style>
@endpush
<x-dashboard-layout>
<div class="content">
  <nav class="mb-3"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('member.index') }}">{{ __('admin_staff.employees') }}</a></li><li class="breadcrumb-item"><a href="{{ route('member.edit',$member) }}#work-calendar">{{ $member->name }}</a></li><li class="breadcrumb-item active">{{ __('admin_staff.exceptions') }}</li></ol></nav>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
  <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4"><div><h2>{{ __('admin_staff.closure_calendar') }}</h2><p class="text-body-tertiary mb-0">{{ __('admin_staff.closure_calendar_hint',['name'=>$member->name]) }}</p></div><button class="btn btn-primary align-self-start" data-bs-toggle="modal" data-bs-target="#createClosure">{{ __('admin_staff.add_exception') }}</button></div>
  <div class="card"><div class="card-body p-2 p-md-4"><div id="closure-calendar"></div></div></div>

  <div class="modal fade" id="createClosure" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('member.closures.store',$member) }}">@csrf<div class="modal-header"><h5 class="modal-title">{{ __('admin_staff.new_exception') }}</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body">@include('admin.member.partials.closure-form',['prefix'=>'create'])</div><div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">{{ __('admin_staff.cancel') }}</button><button class="btn btn-primary">{{ __('admin_staff.save') }}</button></div></form></div></div>
<div class="modal fade" id="editClosure" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="editClosureForm" method="POST">@csrf @method('PUT')<div class="modal-header"><h5 class="modal-title">{{ __('admin_staff.edit_exception') }}</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body">@include('admin.member.partials.closure-form',['prefix'=>'edit'])</div><div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">{{ __('admin_staff.cancel') }}</button><button class="btn btn-primary">{{ __('admin_staff.save') }}</button></div></form><form id="deleteClosureForm" method="POST" class="px-3 pb-3" onsubmit='return confirm(@js(__('admin_staff.delete_exception')))'>@csrf @method('DELETE')<button class="btn btn-outline-danger w-100">{{ __('admin_staff.delete') }}</button></form></div></div></div>
  <x-dashboard-footer />
</div>
@push('scripts')
<script src="{{ asset('vendors/fullcalendar/index.global.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded',function(){
 const calendar=new FullCalendar.Calendar(document.getElementById('closure-calendar'),{initialView:'dayGridMonth',firstDay:1,height:'auto',locale:@json(app()->getLocale()),headerToolbar:{left:'prev,next today',center:'title',right:'dayGridMonth,timeGridWeek,listMonth'},buttonText:{today:@json(__('admin_staff.today')),month:@json(__('admin_staff.month')),week:@json(__('admin_staff.week')),list:@json(__('admin_staff.list'))},selectable:true,events:@json(route('member.closures.events',$member)),select(info){document.getElementById('create_date').value=info.startStr.slice(0,10);document.getElementById('create_date_to').value=info.startStr.slice(0,10);bootstrap.Modal.getOrCreateInstance(document.getElementById('createClosure')).show();},eventClick(info){const p=info.event.extendedProps;if(!p.editable){alert(@json(__('admin_staff.company_exception_edit_hint')));return;}const base=@json(url('/member/'.$member->id.'/closures'));document.getElementById('editClosureForm').action=base+'/'+info.event.id;document.getElementById('deleteClosureForm').action=base+'/'+info.event.id;['type','name','description','date','date_to','start_time','end_time'].forEach(key=>{const el=document.getElementById('edit_'+key);if(el)el.value=p[key]||'';});document.getElementById('edit_full_day').checked=!!p.full_day;document.getElementById('edit_repeat').checked=!!p.repeat;toggleTimes('edit');bootstrap.Modal.getOrCreateInstance(document.getElementById('editClosure')).show();}});calendar.render();
 ['create','edit'].forEach(prefix=>{document.getElementById(prefix+'_full_day').addEventListener('change',()=>toggleTimes(prefix));toggleTimes(prefix);});
});
function toggleTimes(prefix){const full=document.getElementById(prefix+'_full_day').checked;document.querySelectorAll('.'+prefix+'-time').forEach(el=>{el.disabled=full;el.required=!full;});}
</script>
@endpush
</x-dashboard-layout>
