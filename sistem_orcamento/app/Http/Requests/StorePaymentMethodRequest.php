<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'super_admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = auth()->user()->role === 'super_admin' ? null : auth()->user()->company_id;
        
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('payment_methods')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
            ],
            'is_active' => 'boolean',
            'allows_installments' => 'boolean',
            'max_installments' => [
                'nullable',
                'integer',
                'min:1',
                'max:60',
                'required_if:allows_installments,true'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do método de pagamento é obrigatório.',
            'name.string' => 'O nome deve ser um texto válido.',
            'name.max' => 'O nome não pode ter mais de 100 caracteres.',
            'name.unique' => 'Já existe um método de pagamento com este nome.',
            'is_active.boolean' => 'O status ativo deve ser verdadeiro ou falso.',
            'allows_installments.boolean' => 'A permissão de parcelamento deve ser verdadeira ou falsa.',
            'max_installments.integer' => 'O número máximo de parcelas deve ser um número inteiro.',
            'max_installments.min' => 'O número mínimo de parcelas é 1.',
            'max_installments.max' => 'O número máximo de parcelas é 60.',
            'max_installments.required_if' => 'O número máximo de parcelas é obrigatório quando o parcelamento está habilitado.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'allows_installments' => $this->boolean('allows_installments'),
            'max_installments' => $this->boolean('allows_installments') ? $this->input('max_installments', 1) : 1
        ]);
    }
}
