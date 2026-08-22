<?php
namespace App\Services;
use App\Models\RedDay;
use App\Models\User;
use App\Models\UserSchedule;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
class EmployeeScheduleManager
{
    public const DAYS = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    public function updateSchedule(User $employee, array $data): UserSchedule
    {
        $values = ['user_id' => $employee->id];
        foreach (self::DAYS as $day) {
            $off = (bool)($data[$day.'_off'] ?? false);
            $start = $data[$day.'_start'] ?? null; $end = $data[$day.'_end'] ?? null;
            if (!$off && (!$start || !$end || $end <= $start)) throw ValidationException::withMessages([$day.'_end' => __('admin_validation.end_after_start')]);
            $values[$day.'_start'] = $off ? null : $start; $values[$day.'_end'] = $off ? null : $end;
        }
        $lunchStart=$data['lunch_start']??null; $lunchEnd=$data['lunch_end']??null;
        if (($lunchStart || $lunchEnd) && (!$lunchStart || !$lunchEnd || $lunchEnd <= $lunchStart)) throw ValidationException::withMessages(['lunch_end'=>__('admin_validation.lunch_invalid')]);
        $values['lunch_start']=$lunchStart; $values['lunch_end']=$lunchEnd;
        return UserSchedule::updateOrCreate(['user_id'=>$employee->id],$values);
    }
    public function saveClosure(User $employee, array $data, ?RedDay $closure = null): RedDay
    {
        if ($closure && $closure->user_id !== $employee->id) abort(404);
        $fullDay=(bool)($data['full_day']??false); $start=$data['start_time']??null; $end=$data['end_time']??null;
        if (!$fullDay && (!$start || !$end || $end <= $start)) throw ValidationException::withMessages(['end_time'=>__('admin_validation.end_after_start')]);
        $values=['user_id'=>$employee->id,'name'=>trim($data['name']),'type'=>$data['type']??'other','description'=>$data['description']??null,'date'=>$data['date'],'date_to'=>$data['date_to']??$data['date'],'full_day'=>$fullDay,'start_time'=>$fullDay?null:$start,'end_time'=>$fullDay?null:$end,'repeat'=>(bool)($data['repeat']??false)];
        if ($closure) {$closure->update($values); return $closure->refresh();}
        return RedDay::create($values);
    }
    public function events(User $employee, Carbon $start, Carbon $end): array
    {
        $closures=RedDay::where(fn($q)=>$q->where('user_id',$employee->id)->orWhereNull('user_id'))->where(fn($q)=>$q->where(function($range)use($start,$end){$range->whereDate('date','<=',$end)->where(fn($until)=>$until->whereNull('date_to')->orWhereDate('date_to','>=',$start));})->orWhere('repeat',true))->get();
        return $closures->flatMap(function(RedDay $item) use($start,$end,$employee){
            $periods=[];$originalStart=Carbon::parse($item->date);$originalEnd=Carbon::parse($item->date_to?:$item->date);$length=$originalStart->diffInDays($originalEnd);
            if($item->repeat){for($year=$start->year-1;$year<=$end->year;$year++){try{$from=$originalStart->copy()->year($year);$to=$from->copy()->addDays($length);if($from->lte($end)&&$to->gte($start))$periods[]=[$from,$to];}catch(\Throwable){}}}else{$periods[]=[$originalStart,$originalEnd];}
            return collect($periods)->flatMap(function(array $period)use($item,$employee){[$from,$to]=$period;$base=['id'=>(string)$item->id,'title'=>'['.$item->typeLabel().'] '.$item->name,'backgroundColor'=>$item->type==='paid_vacation'?'#198754':($item->user_id===$employee->id?'#dc3545':'#6c757d'),'borderColor'=>$item->type==='paid_vacation'?'#198754':($item->user_id===$employee->id?'#dc3545':'#6c757d'),'extendedProps'=>['editable'=>$item->user_id===$employee->id,'date'=>$from->format('Y-m-d'),'date_to'=>$to->format('Y-m-d'),'type'=>$item->type,'name'=>$item->name,'description'=>$item->description,'start_time'=>$item->start_time?substr($item->start_time,0,5):null,'end_time'=>$item->end_time?substr($item->end_time,0,5):null,'full_day'=>(bool)$item->full_day,'repeat'=>(bool)$item->repeat]];if($item->full_day)return[[...$base,'start'=>$from->format('Y-m-d'),'end'=>$to->copy()->addDay()->format('Y-m-d'),'allDay'=>true]];$events=[];for($day=$from->copy();$day->lte($to);$day->addDay())$events[]=[...$base,'title'=>$base['title'].' '.substr($item->start_time,0,5).'–'.substr($item->end_time,0,5),'start'=>$day->format('Y-m-d').'T'.substr($item->start_time,0,8),'end'=>$day->format('Y-m-d').'T'.substr($item->end_time,0,8),'allDay'=>false];return $events;});
        })->values()->all();
    }
}
