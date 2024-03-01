<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupportRequestRequest extends FormRequest
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
        return [
            'title'             => 'required',
            'description'       => 'required',
            'response'          => 'nullable',
            'reason_refusal'    => 'nullable',
            'type'              => 'required',
            'priority'          => 'required',
            'support_user_id'   => 'nullable',
            'start_date'        => 'nullable',
            'conclusion_date'   => 'nullable',
            'cancellation_date' => 'nullable',
            'refusal_date'      => 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'title.required'       => 'O nome é obrigatório',
            'description.required' => 'O nome legível é obrigatório',
            'type'                 => 'O tipo da solicitação deve ser informado',
            'priority'             => 'A prioridade da solicaitação deve ser informada',
        ];
    }
}
