<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|alpha|min:3',
            'email' => 'required|email',
            'password' => 'required'
        ];
    }

    public function messages() {
        return [
            'name.required' => 'Поле Имя обязателено.',
            'name.alpha' => 'Имя должно содержать только символы.',
            'name.min' => 'Имя должно быть минимум из трех символов.',
            'email.required' => 'Поле Email обязательно.',
            'password.required' => 'Заполните пароль.'
        ];
    }
}
