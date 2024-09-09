<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskFormRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
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
    public function store(TaskFormRequest $request)
    {
        $user = Auth::user(); // Get the currently authenticated user

        // Create the task using validated data from the request
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'status' => "pending",
            'assigned_to' => $request->assigned_to,
            'created_by' => $user->user_id,
        ]);
        return ApiResponse::success($task, 'Task created successfully', 201);
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
    public function update(TaskFormRequest $request,  Task $task)
    {
        // Check if the user is authorized based on the request's authorize method
        if (!$request->authorize()) {
            return ApiResponse::error('It is not allowed for you', 403, 'Unauthorized');
        }

        // Get validated data from the request
        $validatedData = $request->validated();

        // For users with role_id == 3 (normal users), we want to allow them to update only the 'status'
        if (Auth::user()->role_id == 3) {
            $validatedData = $request->only(['status']); // Limit the update to the 'status' field
        }

        // Update the task with validated data
        $task->update($validatedData);

        // Return a success response
        return ApiResponse::success('Task updated successfully', $task);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
