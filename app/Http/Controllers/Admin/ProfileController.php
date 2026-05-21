<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    private ProfileService $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->profileService->getProfile();
        
        return view('admin.profile.index', [
            'profile' => $data['profile'],
            'services' => $data['services'],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
    public function update(ProfileRequest $request)
    {
        if (User::where('username', '@'.$request->username)->exists()) {
            return redirect()->back()->withErrors(['username' => 'Такой никнейм уже существует.'])->withInput();
        }

        $data = $this->profileService->setProfile($request);
         
        return response()->json([
            'profile' => $data,
            'status' => 'success',
            'message' => 'Данные обновились успешно'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
