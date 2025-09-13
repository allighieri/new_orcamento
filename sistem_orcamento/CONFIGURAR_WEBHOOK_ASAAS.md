# Configuração do Webhook no Painel Asaas

## Problema Identificado

O sistema está gerando pagamentos PIX corretamente e salvando na tabela `payments`, mas:

1. ✅ **CORRIGIDO**: A coluna `extra_budgets_quantity` estava com valor fixo 999, agora usa o `budget_limit` do plano
2. ❌ **PENDENTE**: O webhook não está configurado no painel Asaas, então a tabela `usage_controls` não é atualizada quando o pagamento é confirmado

## URL do Webhook

**URL Pública (ngrok)**: `https://7b58e4db0278.ngrok-free.app/webhook/asaas`

**URL Local (desenvolvimento)**: `http://localhost:8000/webhook/asaas`

## Como Configurar no Painel Asaas

### 1. Acessar o Painel Asaas
- Acesse: https://sandbox.asaas.com (ambiente de testes)
- Faça login com suas credenciais

### 2. Configurar Webhook
1. Vá em **Configurações** → **Webhooks**
2. Clique em **Adicionar Webhook**
3. Configure:
   - **URL**: `https://7b58e4db0278.ngrok-free.app/webhook/asaas`
   - **Eventos**: Selecione os seguintes eventos:
     - ✅ `PAYMENT_RECEIVED` (Pagamento recebido)
     - ✅ `PAYMENT_CONFIRMED` (Pagamento confirmado)
     - ✅ `PAYMENT_OVERDUE` (Pagamento vencido)
     - ✅ `PAYMENT_DELETED` (Pagamento excluído)
     - ✅ `PAYMENT_REFUNDED` (Pagamento estornado)
   - **Método**: POST
   - **Content-Type**: application/json

### 3. Testar Webhook

Após configurar, teste o webhook:

```bash
# 1. Gerar um novo pagamento PIX
php test_extra_payment.php

# 2. Simular pagamento no painel Asaas
# Vá em Cobranças → Encontre o pagamento → Marcar como "Recebido"

# 3. Verificar se o webhook foi chamado
tail -f storage/logs/laravel.log | grep webhook

# 4. Verificar se usage_controls foi atualizado
php check_usage_control.php
```

## Verificação do Funcionamento

### 1. Webhook Funcionando
Quando o webhook estiver configurado corretamente, você verá nos logs:

```
[2025-09-12 22:05:18] local.INFO: Webhook Asaas recebido
[2025-09-12 22:05:18] local.INFO: Pagamento aprovado via webhook - Cache definido
[2025-09-12 22:05:18] local.INFO: Orçamentos extras adicionados com sucesso
```

### 2. Usage Controls Atualizado
Após o pagamento ser confirmado:

```bash
php check_usage_control.php
```

Deve mostrar:
```
Extra Budgets Purchased: 10 (ou o valor do budget_limit do plano)
Extra Amount Paid: R$ 30.00
Total Available: 20 (budgets_limit + extra_budgets_purchased)
```

## Fluxo Completo Corrigido

1. **Usuário compra orçamentos extras**
   - ✅ Sistema gera pagamento com `extra_budgets_quantity` = `budget_limit` do plano
   - ✅ Integra com Asaas e gera cobrança PIX
   - ✅ Salva pagamento na tabela `payments` com status `PENDING`

2. **Usuário paga o PIX**
   - ✅ Asaas detecta o pagamento
   - ❌ **PENDENTE**: Asaas chama webhook configurado
   - ❌ **PENDENTE**: Sistema processa webhook e atualiza `usage_controls`

3. **Sistema atualiza controles**
   - ❌ **PENDENTE**: `usage_controls.extra_budgets_purchased` += quantidade paga
   - ❌ **PENDENTE**: `usage_controls.extra_amount_paid` += valor pago
   - ❌ **PENDENTE**: Status do pagamento atualizado para `RECEIVED`

## Arquivos Corrigidos

### PaymentController.php
- ✅ Linha 399: `extra_budgets_quantity` agora usa `$activeSubscription->plan->budget_limit`
- ✅ Linha 402: `extra_budgets_quantity` agora usa `$activeSubscription->plan->budget_limit`

### WebhookController.php
- ✅ Método `handleExtraBudgetsPayment` implementado corretamente
- ✅ Atualiza `usage_controls` quando pagamento é confirmado

## Próximos Passos

1. **URGENTE**: Configurar webhook no painel Asaas com a URL: `https://7b58e4db0278.ngrok-free.app/webhook/asaas`
2. Testar fluxo completo de pagamento
3. Verificar se `usage_controls` é atualizado automaticamente
4. Documentar processo para produção

## Observações Importantes

- A URL do ngrok (`https://7b58e4db0278.ngrok-free.app`) é temporária
- Para produção, use a URL real do servidor
- O webhook deve estar acessível publicamente
- Verifique se não há firewall bloqueando as requisições do Asaas

---

**Status**: ✅ Código corrigido, ❌ Webhook não configurado no Asaas
**Próxima ação**: Configurar webhook no painel Asaas