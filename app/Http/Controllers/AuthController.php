<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthFormRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(AuthFormRequest $request)
    {
        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password
        ]);

        $token = JWTAuth::fromUser($newUser);

        return ApiResponse::success(compact('newUser', 'token'), 'User created successfully', 201);
    }

    public function login(AuthFormRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $token = JWTAuth::attempt($credentials);
        // Attempt to authenticate the user with the provided credentials
        if (!$token)
            return ApiResponse::error('كلمة السر غير صحيحة', 401);

        // Retrieve the authenticated user
        $user = Auth::user();

        // Return a successful response with the user and token
        return ApiResponse::success(compact('user', 'token'), 'User logged in successfully', 200);
    }

    public function logout()
    {
        try {
            // Invalidate the token, so it can no longer be used
            JWTAuth::invalidate(JWTAuth::getToken());

            return ApiResponse::success(null, 'User logged out successfully', 200);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $exception) {
            // Something went wrong while attempting to invalidate the token
            return ApiResponse::error('Failed to log out, please try again', 500);
        }
    }
}
