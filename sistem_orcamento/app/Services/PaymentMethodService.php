<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\Company;

class PaymentMethodService
{
    /**
     * Obter métodos de pagamento disponíveis para uma empresa
     * Inclui métodos globais + métodos específicos da empresa
     */
    public function getAvailablePaymentMethods($companyId = null)
    {
        return PaymentMethod::active()
            ->whereNull('deleted_at')
            ->forCompany($companyId)
            ->join('payment_option_methods', 'payment_methods.payment_option_method_id', '=', 'payment_option_methods.id')
            ->orderBy('payment_option_methods.method')
            ->select('payment_methods.*')
            ->get();
    }

    /**
     * Obter apenas métodos globais
     */
    public function getGlobalPaymentMethods()
    {
        return PaymentMethod::active()
            ->whereNull('deleted_at')
            ->global()
            ->join('payment_option_methods', 'payment_methods.payment_option_method_id', '=', 'payment_option_methods.id')
            ->orderBy('payment_option_methods.method')
            ->select('payment_methods.*')
            ->get();
    }

    /**
     * Obter apenas métodos específicos de uma empresa
     */
    public function getCompanySpecificPaymentMethods($companyId)
    {
        return PaymentMethod::active()
            ->whereNull('deleted_at')
            ->where('company_id', $companyId)
            ->join('payment_option_methods', 'payment_methods.payment_option_method_id', '=', 'payment_option_methods.id')
            ->orderBy('payment_option_methods.method')
            ->select('payment_methods.*')
            ->get();
    }

    /**
     * Criar um novo método de pagamento para uma empresa
     */
    public function createCompanyPaymentMethod($companyId, array $data)
    {
        $data['company_id'] = $companyId;
        
        return PaymentMethod::create($data);
    }

    /**
     * Criar um método de pagamento global
     */
    public function createGlobalPaymentMethod(array $data)
    {
        $data['company_id'] = null;
        
        return PaymentMethod::create($data);
    }

    /**
     * Verificar se um método de pagamento pode ser usado por uma empresa
     */
    public function canCompanyUsePaymentMethod($companyId, $paymentMethodId)
    {
        $paymentMethod = PaymentMethod::find($paymentMethodId);
        
        if (!$paymentMethod || !$paymentMethod->is_active) {
            return false;
        }
        
        // Pode usar se for global ou se pertencer à empresa
        return $paymentMethod->is_global || $paymentMethod->company_id == $companyId;
    }

    /**
     * Obter métodos que permitem parcelamento para uma empresa
     */
    public function getInstallmentPaymentMethods($companyId = null)
    {
        return PaymentMethod::active()
            ->whereNull('deleted_at')
            ->forCompany($companyId)
            ->allowsInstallments()
            ->join('payment_option_methods', 'payment_methods.payment_option_method_id', '=', 'payment_option_methods.id')
            ->orderBy('payment_option_methods.method')
            ->select('payment_methods.*')
            ->get();
    }

    /**
     * Duplicar métodos globais para uma empresa específica
     * Útil quando uma empresa quer customizar métodos padrão
     */
    public function duplicateGlobalMethodsForCompany($companyId, array $methodSlugs = [])
    {
        $query = PaymentMethod::global()->active();
        
        if (!empty($methodSlugs)) {
            $query->whereIn('slug', $methodSlugs);
        }
        
        $globalMethods = $query->get();
        $duplicatedMethods = [];
        
        foreach ($globalMethods as $method) {
            $newMethod = $method->replicate();
            $newMethod->company_id = $companyId;
            $newMethod->slug = $method->slug . '-' . $companyId; // Evitar conflito de slug
            $newMethod->save();
            
            $duplicatedMethods[] = $newMethod;
        }
        
        return $duplicatedMethods;
    }

    /**
     * Estatísticas de uso de métodos de pagamento por empresa
     */
    public function getPaymentMethodStats($companyId = null)
    {
        $query = PaymentMethod::withCount('budgetPayments');
        
        if ($companyId) {
            $query->forCompany($companyId);
        }
        
        return $query->get()->map(function ($method) {
            return [
                'id' => $method->id,
                'name' => $method->name,
                'is_global' => $method->is_global,
                'company_id' => $method->company_id,
                'usage_count' => $method->budget_payments_count,
                'allows_installments' => $method->allows_installments,
                'max_installments' => $method->max_installments,
            ];
        });
    }
}