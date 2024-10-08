<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\{TaskController, UserController};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(AuthController::class)
    ->prefix('auth')
    ->group(function () {
        Route::post('register', 'register')->name('auth.register');
        Route::post('login', 'login')->name('auth.login');
        Route::post('logout', 'logout')->name('auth.logout')->middleware('auth:api'); //This middleware ensures that the user is authenticated via a JWT token
    });

Route::middleware(['auth:api'])->group(function () {

    //manage tasks
    Route::post('/tasks', [TaskController::class, 'store']); //done
    Route::get('/tasks', [TaskController::class, 'index']); //done
    Route::get('/tasks/{task}', [TaskController::class, 'show']); //done
    Route::put('/tasks/{task}', [TaskController::class, 'update']); //done
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

    //assigned task
    Route::post('/tasks/{task}/{assigned_to}', [TaskController::class, 'assigned']); //done

    //manage users
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});
