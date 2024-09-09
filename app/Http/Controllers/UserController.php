<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserFormRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserFormRequest  $request)
    {
        // Only admin can create a new user
        $user = Auth::user();
        if ($user->role_id != 1) {
            return ApiResponse::error('Unauthorized', 403, 'Only admins can create new users.');
        }

        // Get validated data from the request
        $validatedData = $request->validated();
        // Create the new user
        $newUser = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),  // Hash the password before saving
            'role_id' => $validatedData['role_id'],
        ]);

        return ApiResponse::success($newUser, 'User created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
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
