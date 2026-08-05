<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\RedDay;
use App\Models\User;
use App\Services\EmployeeScheduleManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
class EmployeeScheduleController extends Controller
{
    public function __construct(private readonly EmployeeScheduleManager $manager) {}
    public function updateSchedule(Request $request, User $member)
    {
        $rules=['lunch_start'=>['nullable','date_format:H:i'],'lunch_end'=>['nullable','date_format:H:i']];
        foreach(EmployeeScheduleManager::DAYS as $day){$rules[$day.'_off']=['nullable','boolean'];$rules[$day.'_start']=['nullable','date_format:H:i'];$rules[$day.'_end']=['nullable','date_format:H:i'];}
        $this->manager->updateSchedule($member,$request->validate($rules));
        return redirect()->route('member.edit',$member)->withFragment('work-calendar')->with('success','Рабочее время сохранено.');
    }
    public function index(User $member) { return view('admin.member.closures',compact('member')); }
    public function events(Request $request, User $member)
    {
        $data=$request->validate(['start'=>['required','date'],'end'=>['required','date']]);$start=Carbon::parse($data['start'])->startOfDay();$end=Carbon::parse($data['end'])->endOfDay();abort_if($start->diffInDays($end)>400,422);
        return response()->json($this->manager->events($member,$start,$end));
    }
    public function store(Request $request, User $member) { $this->manager->saveClosure($member,$this->closureData($request)); return back()->with('success','Исключение добавлено.'); }
    public function update(Request $request, User $member, RedDay $closure) { $this->manager->saveClosure($member,$this->closureData($request),$closure); return back()->with('success','Исключение обновлено.'); }
    public function destroy(User $member, RedDay $closure) { abort_unless($closure->user_id===$member->id,404);$closure->delete();return back()->with('success','Исключение удалено.'); }
    private function closureData(Request $request): array { return $request->validate(['name'=>['required','string','max:190'],'type'=>['required',\Illuminate\Validation\Rule::in(array_keys(RedDay::TYPES))],'description'=>['nullable','string','max:500'],'date'=>['required','date_format:Y-m-d'],'date_to'=>['nullable','date_format:Y-m-d','after_or_equal:date'],'full_day'=>['nullable','boolean'],'start_time'=>['nullable','date_format:H:i'],'end_time'=>['nullable','date_format:H:i'],'repeat'=>['nullable','boolean']]); }
}
