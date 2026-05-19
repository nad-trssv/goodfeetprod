<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\StoreRequest;
use App\Http\Resources\MemberResource;
use App\Models\Events;
use App\Services\MemberService;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    private MemberService $memberService;

    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    public function index()
    {
        $data = $this->memberService->list();
        $members = MemberResource::collection($data['members']);
         
        return view('admin.member.index', [
            'members' => $members,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Illuminate\View\View
    {
        $data = $this->memberService->create();

        return view('admin.member.create', [
            'roles' => $data['roles'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request, $step)
{  
    if ($step == 1) {
        if ($request->hasFile('photo')) {
            $path = $request['photo']->store('users', 'public');
        } else {
            $path = null;
        }
    
        $data = [
            'name' => $request->name,
            'photo' => $path.'',
            'username' => $request->username,
            'phone' =>  $request->phone,
            'role_id' =>  $request->role_id,
            'date_birthday' => $request->date_birthday,
        ];

        return response()->json(['data' => $data], 200);
    } elseif ($step == 2) {
        $data = $this->memberService->store($request);
        
        if($data['member']->date_birthday){
            Events::create([
                'name' => 'Sünnipäev',
                'date' => $request->date_birthday,
                'user_id' => $data['member']->id,
                'repeat' => 1,
            ]);
        }
        return response()->json(['message' => 'Данные успешно сохранены', 'token' => $data['token']], 200);
    }
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
