<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CrmSettingsRequest;
use App\Models\CrmChatStaff;
use App\Models\CrmTag;
use App\Models\SiteSettings;
use App\Models\User;
use App\Services\Crm\CrmChatSettings;
use App\Services\Localization\SiteLocaleRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CrmSettingsController extends Controller
{
    public function index(Request $request, CrmChatSettings $settings, SiteLocaleRegistry $locales): View
    {
        $this->authorizeAdmin($request);
        $staff=User::with(['role.permissions','chatSettings'])->get()->filter->isStaff()->sortBy('name')->values();
        $tags=CrmTag::withCount('customers')->orderBy('name')->get();
        return view('admin.crm.settings', [
            'settings'=>$settings->all(),
            'staff'=>$staff,
            'tags'=>$tags,
            'siteLocales'=>$locales->installed(),
            'defaultSiteLocale'=>$locales->defaultCode(),
        ]);
    }

    public function update(CrmSettingsRequest $request): RedirectResponse
    {
        $data=$request->validated();
        DB::transaction(function() use($data){
            foreach(['enabled','notify_client_staff_events','title','welcome_message','offline_message','timezone','schedule'] as $key){
                SiteSettings::updateOrCreate(['key'=>'crm_chat_'.$key],['group'=>'crm_chat','payload'=>json_encode($data[$key],JSON_UNESCAPED_UNICODE)]);
            }
            $ids=collect($data['staff']??[])->pluck('user_id')->map(fn($id)=>(int)$id);
            CrmChatStaff::whereNotIn('user_id',$ids)->delete();
            foreach($data['staff']??[] as $row){
                CrmChatStaff::updateOrCreate(['user_id'=>$row['user_id']],[
                    'is_enabled'=>(bool)($row['is_enabled']??false),
                    'can_view_history'=>(bool)($row['can_view_history']??false),
                    'must_answer'=>(bool)($row['must_answer']??false),
                ]);
            }
        });
        return back()->with('success',__('crm.settings_saved'));
    }

    public function storeTag(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data=$request->validate(['name'=>['required','string','max:80','unique:crm_tags,name'],'color'=>['required','regex:/^#[0-9a-fA-F]{6}$/']]);
        CrmTag::create($data+['slug'=>Str::slug($data['name']).'-'.Str::lower(Str::random(5))]);
        return back()->with('success',__('crm.tag_added'));
    }

    public function updateTag(Request $request, CrmTag $tag): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data=$request->validate(['name'=>['required','string','max:80',Rule::unique('crm_tags','name')->ignore($tag)],'color'=>['required','regex:/^#[0-9a-fA-F]{6}$/']]);
        $tag->update($data);
        return back()->with('success',__('crm.tag_updated'));
    }

    public function destroyTag(Request $request, CrmTag $tag): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $tag->delete();
        return back()->with('success',__('crm.tag_deleted'));
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()->hasAllAppointmentsScope() && $request->user()->hasPermission('crm.settings'),403);
    }
}
