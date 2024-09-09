<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuthFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Determine the mode (login or register)
        $route = $this->route()->getName();
        if ($route === 'auth.register') {
            return $this->registrationRules();
        } elseif ($route === 'auth.login') {
            return $this->loginRules();
        }

        return [];
    }
    //----------------------------------------------------------------------------------------
    /**
     * Validation rules for registration.
     *
     * @return array
     */
    protected function registrationRules()
    {
        return [

            'name' => [
                'required',
                'string',
                'between:2,255',
                'regex:/^[A-Za-z\s\-\_]+$/'
            ],
            // The 'name' field is required, must be a string, between 2 and 255 characters long,
            // and can only contain letters, spaces, and hyphens.

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],
            // The 'email' field is required, must be a valid email address, max 255 characters,
            // and must be unique in the 'users' table, email column.

            'password' => [
                'required',
                'string',
                'min:8', // Minimum length of 8 characters
                'regex:/[a-z]/',     // At least one lowercase letter
                'regex:/[A-Z]/',     // At least one uppercase letter
                'regex:/[0-9]/',     // At least one digit
                'regex:/[@$!%*?&-_]/', // At least one special character
            ],
            // The 'password' field is required, must be a string, and should adhere to Laravel's password rule.
        ];
    }
    //----------------------------------------------------------------------------------------
    /**
     * Validation rules for login.
     *
     * @return array
     */
    protected function loginRules()
    {
        return [
            'email' => 'required|string|email|exists:users,email',
            'password' => 'required|string',
        ];
    }
    //----------------------------------------------------------------------------------------
    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        // Customize the failed validation response.
        throw new HttpResponseException(
            ApiResponse::error(
                'Validation errors occurred .',
                422
            )
        );
    }
    //----------------------------------------------------------------------------------------
    /**
     * Customize attribute names for error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'email' => 'البريد الالكتروني',
            'password' => 'كلمة السر',
        ];
    }
    //----------------------------------------------------------------------------------------
    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            // Custom messages for the 'name' field
            'name.required' => 'حقل :attribute مطلوب.',
            'name.string' => 'حقل :attribute يجب أن يكون نصًا صحيحًا.',
            'name.between' => 'حقل :attribute يجب أن يكون بين 2 و 255 حرفًا.',
            'name.regex' => 'حقل :attribute يمكن أن يحتوي فقط على أحرف، مسافات، وشرطات.',

            // Custom messages for the 'email' field
            'email.required' => 'حقل :attribute مطلوب.',
            'email.string' => 'حقل :attribute يجب أن يكون نصًا صحيحًا.',
            'email.email' => 'حقل :attribute يجب أن يكون بريدًا إلكترونيًا صحيحًا.',
            'email.max' => 'حقل :attribute يجب ألا يتجاوز 255 حرفًا.',
            'email.unique' => 'حقل :attribute مستخدم بالفعل.',
            'email.exists' => 'حقل :attribute غير صحيح',

            // Custom messages for the 'password' field
            'password.required' => 'حقل :attribute مطلوب.',
            'password.string' => 'حقل :attribute يجب أن يكون نصًا صحيحًا.',
            'password.min' => 'حقل :attribute يجب أن يكون على الأقل 8 أحرف.',
            'password.regex' => 'حقل :attribute يجب أن يحتوي على حرف صغير واحد على الأقل، وحرف كبير واحد على الأقل، ورقم واحد على الأقل، ورمز خاص واحد على الأقل (@$!%*?&-_).',
        ];
    }
}
