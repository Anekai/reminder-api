<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        switch ($this->method()) {
            case ('POST'):
                return [
                    'name'     => 'required',
                    'email'    => 'required|email|unique:users,email',
                    'password' => 'required',
                    'role_id'  => 'required',
                ];
            break;

            case ('PUT'):
                return [
                    'name'     => 'required',
                    'email'    => 'required|email|unique:users,email,' . $this->get('id'),
                    'password' => 'nullable',
                    'role_id'  => 'required',
                ];
            break;
        }
    }

    public function messages()
    {
        return [
            'nome.required'     => 'O nome é obrigatório',
            'email.required'    => 'O email é obrigatório',
            'email.email'       => 'O email deve ser válido',
            'email.unique'      => 'Este email já está cadastrado',
            'password.required' => 'A senha é obrigatória',
            'role_id.required'  => 'O tipo do usuário é obrigatório',
        ];
    }
}
