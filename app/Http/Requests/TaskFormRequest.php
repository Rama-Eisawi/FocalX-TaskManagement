<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Validator;
use App\Http\Responses\ApiResponse;
use App\Models\Task;
use Illuminate\Http\Exceptions\HttpResponseException;

class TaskFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();  // Fetch authenticated user

        // Check if it's a creation request (POST) or an update request (PUT/PATCH)
        if ($this->isMethod('post')) {
            // Creation request: only Admin or Manager can create tasks
            return $user && ($user->role_id == 1 || $user->role_id == 2);
        }

        // For update requests (PUT or PATCH)
        if ($this->isMethod('put') || $this->isMethod('patch')) {

            $task = $this->route('task'); // $task is already an instance of Task
            Log::info('Updating Task with ID:', ['task_id' => $task->id]);

            if (!$task) {
                return false; // Task not found
            }

            if ($user->role_id == 1) {
                // Admins can update any task
                return true;
            }

            if ($user->role_id == 2) {
                // Managers can update only tasks they created
                return $task->created_by == $user->user_id;
            }

            if ($user->role_id == 3) {
                // Users can update only the status of tasks assigned to them
                return $task->assigned_to == $user->user_id && $this->has('status');
            }
        }

        return false; // Default deny
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $this->isMethod('put') ? $isUpdate = true : $isUpdate = false;
        return [
            'title' => [$isUpdate ? 'sometimes' : 'required', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => [$isUpdate ? 'sometimes' : 'required', 'required', 'in:low,medium,high'],
            'due_date' => [$isUpdate ? 'sometimes' : 'required', 'required', 'date_format:d-m-Y H:i'],
            'assigned_to' => [$isUpdate ? 'sometimes' : 'required', 'required',  'exists:users,user_id'],
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();

        // Customize the failed validation response.
        throw new HttpResponseException(
            ApiResponse::error(
                'Validation errors occurred.',
                422,
                $errors
            )
        );
    }
    /**
     * Customize attribute names for error messages.
     */
    public function attributes(): array
    {
        return [
            'title' => 'عنوان المهمة',
            'description' => 'توصيف المهمة',
            'priority' => 'أولوية المهمة',
            'due_date' => 'تاريخ الاستحقاق',
            'assigned_to' => 'موكلة الى',
        ];
    }
    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            // Custom messages for the 'title' field
            'title.required' => 'حقل :attribute مطلوب.',
            'title.string' => 'حقل :attribute يجب أن يكون نصًا صحيحًا.',
            'title.max' => 'حقل :attribute يجب ألا يتجاوز 255 حرفًا.',

            // Custom messages for the 'description' field
            'description.string' => 'حقل :attribute يجب أن يكون نصًا صحيحًا.',

            // Custom messages for the 'priority' field
            'priority.required' => 'حقل :attribute مطلوب.',
            'priority.in' => 'حقل :attribute يجب أن يكون أحد القيم: منخفضة، متوسطة، عالية.',

            // Custom messages for the 'due_date' field
            'due_date.required' => 'حقل :attribute مطلوب.',
            'due_date.date_format' => 'حقل :attribute لا يتطابق مع التنسيق المطلوب Y-m-d H:i.',

            // Custom messages for the 'assigned_to' field
            'assigned_to.required' => 'حقل :attribute مطلوب.',
            'assigned_to.exists' => 'القيمة المحددة لـ :attribute غير صحيحة.',
        ];
    }
}
