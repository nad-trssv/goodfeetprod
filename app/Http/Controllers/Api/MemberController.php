<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\StoreRequest;
use App\Services\Api\MemberService;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    private MemberService $memberService;

    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(StoreRequest $request, $step)
    {  
        if ($step == 1) {
            $path = $request['photo']->store('users', 'public');
            $data = [
                'name' => $request->name,
                'photo' => $path.'',
                'username' => $request->username,
                'phone' =>  $request->phone,
                'date_birthday' =>  $request->date_birthday,
            ];
            return response()->json(['data' => $data], 200);
        } elseif ($step == 2) {
            $this->memberService->store($request);
            return response()->json(['message' => 'Данные успешно сохранены'], 200);
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
