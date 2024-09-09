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
        $user = Auth::user();
        if ($user->role_id != 1) {
            return ApiResponse::error('Unauthorized', 403, 'Only admins can view all users.');
        }

        $users = User::all();
        return ApiResponse::success($users, 'Users retrieved successfully', 200);
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
    public function show($id)
    {
        $user = Auth::user();

        // Allow only admin to see a specific user
        if ($user->role_id != 1) {
            return ApiResponse::error('Unauthorized', 403, 'Only admins can view a specific user.');
        }

        // Retrieve user (with soft-deleted check)
        $user = User::findOrFail($id);

        return ApiResponse::success($user, 'User retrieved successfully', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserFormRequest $request, $id)
    {
        $user = Auth::user();
        // Allow only admin to update a user
        if ($user->role_id != 1) {
            return ApiResponse::error('Unauthorized', 403, 'Only admins can update users.');
        }

        // Retrieve and update the user
        $userToUpdate = User::findOrFail($id);
        $userToUpdate->update($request->validated());

        return ApiResponse::success($userToUpdate, 'User updated successfully', 200);
    }

    /**
     * Soft Delete the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        // Only admin can soft delete a user
        if ($user->role_id != 1) {
            return ApiResponse::error('Unauthorized', 403, 'Only admins can delete users.');
        }

        // Find the user and soft delete
        $userToDelete = User::findOrFail($id);
        $userToDelete->delete();

        return ApiResponse::success(null, 'User deleted successfully', 200);
    }
}
