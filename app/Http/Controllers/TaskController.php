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
    public function index(Request $request)
    {
        $user = Auth::user();
        $priority = $request->query('priority');
        $status = $request->query('status');
        $query = Task::query();
        if ($user->role_id == 3) {
            $query->where('assigned_to', $user->user_id);
        }
        if ($user->role_id == 2) {
            $query->where('created_by', $user->user_id);
        }

        // Apply the filter if the 'priority' parameter is provided
        if ($priority) {
            $query->byPriority($priority);
        }

        // Apply the filter if the 'status' parameter is provided
        if ($status) {
            $query->byStatus($status);
        }

        // Fetch tasks (soft-deleted tasks are automatically excluded)
        $tasks = $query->get();

        return ApiResponse::success($tasks, 'Tasks retrieved successfully', 200);
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
    public function show(Request $request, $id)
    {
        // Fetch the authenticated user
        $user = Auth::user();

        // Find the task by ID
        $task = Task::find($id);

        if (!$task) {
            return ApiResponse::error('Task not found.', 404, '');
        }

        // Check if the user is an admin
        if ($user->role_id == 1) {
            // Admins can view any task
            return ApiResponse::success($task, 'Task retrieved successfully', 200);
        }

        // Check if the user is a manager
        if ($user->role_id == 2) {
            // Managers can view only tasks they created
            if ($task->created_by == $user->user_id) {
                return ApiResponse::success($task, 'Task retrieved successfully', 200);
            }
            return ApiResponse::error('You are not authorized to view this task.', 403, 'unauthenticated');
        }

        // Check if the user is a regular user
        if ($user->role_id == 3) {
            // Users can view only tasks assigned to them
            if ($task->assigned_to == $user->user_id) {
                return ApiResponse::success($task, 'Task retrieved successfully', 200);
            }
            return ApiResponse::error('You are not authorized to view this task.', 403, 'unauthenticated');
        }

        // For other roles, deny access
        return ApiResponse::error('You are not authorized to view this task.', 403, 'unauthenticated');
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

    public function assigned(Task $task, $assigned_to)
    {
        // Fetch the authenticated user
        $user = Auth::user();
        // Check if the user is not a manager
        if ($user->role_id != 2) {
            return ApiResponse::error('Unauthorized', 403, 'You are not allowed to assign tasks.');
        }
        // For managers, ensure they can only assign tasks they created or that were created by admins
        if ($task->created_by != $user->user_id && $task->created_by != 1) {
            return ApiResponse::error('Unauthorized', 403, 'You can only assign tasks you created or tasks created by admins.');
        }

        // Update the task's assigned_to field
        $task->assigned_to = $assigned_to;
        $task->save();

        return ApiResponse::success('Task assigned successfully', $task);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $user = Auth::user();

        // Check if the user is an admin or the manager who created the task
        if ($user->role_id == 1 || ($user->role_id == 2 && $task->created_by == $user->user_id)) {
            $task->delete(); // Soft delete the task
            return ApiResponse::success('Task deleted successfully', $task);
        }

        return ApiResponse::error('Unauthorized', 403, 'You are not allowed to delete this task.');
    }
}
