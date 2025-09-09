<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $paymentMethod = $this->route('payment_method');
        $user = auth()->user();
        
        // Verificar se o usuário está autenticado
        if (!$user) {
            return false;
        }
        
        // Verificar se o método de pagamento existe
        if (!$paymentMethod) {
            return false;
        }
        
        // Super admin pode editar qualquer método
        if ($user->role === 'super_admin') {
            return true;
        }
        
        // Admin só pode editar métodos da própria empresa ou métodos globais
        if ($user->role === 'admin') {
            return $paymentMethod->company_id === null || $paymentMethod->company_id === $user->company_id;
        }
        
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $paymentMethod = $this->route('payment_method');
        $user = auth()->user();
        
        // Verificar se o usuário está autenticado
        if (!$user) {
            abort(401);
        }
        
        // Verificar se o método de pagamento existe
        if (!$paymentMethod) {
            abort(404);
        }
        
        // Determinar company_id para validação
        $companyId = $user->role === 'super_admin' ? $this->input('company_id') : $paymentMethod->company_id;
        
        $rules = [
            'payment_option_method_id' => [
                'required',
                'integer',
                'exists:payment_option_methods,id',
                Rule::unique('payment_methods', 'payment_option_method_id')
                    ->where('company_id', $companyId)
                    ->ignore($paymentMethod->id)
                    ->whereNull('deleted_at')
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
        
        // Super admin pode alterar a empresa
        if ($user->role === 'super_admin') {
            $rules['company_id'] = [
                'required',
                'integer',
                'exists:companies,id'
            ];
        }
        
        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'payment_option_method_id.required' => 'É obrigatório selecionar um método de pagamento.',
            'payment_option_method_id.integer' => 'O método de pagamento deve ser um número válido.',
            'payment_option_method_id.exists' => 'O método de pagamento selecionado não existe.',
            'payment_option_method_id.unique' => 'Este método de pagamento já está cadastrado para esta empresa.',
            'company_id.required' => 'A empresa é obrigatória.',
            'company_id.integer' => 'A empresa deve ser um número válido.',
            'company_id.exists' => 'A empresa selecionada não existe.',
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
