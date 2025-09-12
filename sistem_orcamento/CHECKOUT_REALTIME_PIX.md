# Sistema de Atualização em Tempo Real - Checkout PIX

## Visão Geral

Este documento explica como funciona o sistema de atualização em tempo real do pagamento PIX diretamente na página de checkout, eliminando a necessidade de uma página separada.

## Fluxo Completo

### 1. Processo de Pagamento PIX

1. **Usuário acessa checkout**: `/payments/checkout/{plan}`
2. **Preenche dados PIX**: Nome, CPF/CNPJ, email, telefone
3. **Clica em "Gerar PIX"**: Formulário é enviado via AJAX
4. **Sistema processa**: 
   - Cria/busca cliente no Asaas
   - Gera cobrança PIX
   - Salva pagamento no banco
   - Retorna QR Code e dados
5. **Interface atualizada**: QR Code é exibido na mesma página
6. **Verificação automática**: JavaScript inicia polling de status
7. **Pagamento detectado**: Interface atualiza automaticamente

### 2. Verificação em Tempo Real

```javascript
// Inicia verificação após gerar PIX
startPaymentStatusCheck(paymentId);

// Verifica a cada 3 segundos
setInterval(() => {
    checkPaymentStatus(paymentId);
}, 3000);

// Timeout de 30 minutos
setTimeout(() => {
    clearInterval(paymentCheckInterval);
}, 30 * 60 * 1000);
```

### 3. Detecção de Pagamento

O sistema verifica o status através de múltiplas fontes:

1. **Cache do Webhook**: Verifica se webhook já processou
2. **Banco de dados**: Status atualizado localmente
3. **API Asaas**: Consulta direta como fallback

## Arquivos Modificados

### 1. `resources/views/payments/checkout.blade.php`

**Principais adições:**

- **Função `startPaymentStatusCheck()`**: Inicia verificação automática
- **Função `checkPaymentStatus()`**: Faz requisições AJAX para verificar status
- **Função `showPaymentSuccess()`**: Atualiza interface quando pago
- **Integração com `processPixPayment`**: Inicia verificação após gerar PIX

**JavaScript adicionado:**
```javascript
// Após sucesso do PIX
if (response.success) {
    showPixQrCode(response.pix_qr_code, response.pix_copy_paste, response.due_date);
    
    // Iniciar verificação de status
    if (response.payment_id) {
        startPaymentStatusCheck(response.payment_id);
    }
}
```

### 2. `routes/web.php`

**Rota adicionada:**
```php
Route::get('payments/check-status/{payment}', 
    [PaymentController::class, 'checkPaymentStatus']
)->name('payments.ajax-check-status');
```

### 3. `app/Http/Controllers/PaymentController.php`

**Método `checkPaymentStatus()` já existente:**
- Verifica cache do webhook
- Consulta banco de dados
- Fallback para API Asaas
- Retorna JSON com status atualizado

## Como Testar

### 1. Teste Manual

1. Acesse: `http://localhost:8000/payments/plans`
2. Escolha um plano
3. Clique em "Assinar Plano"
4. Preencha dados PIX
5. Clique em "Gerar PIX"
6. **Observe**: QR Code aparece na mesma página
7. **Aguarde**: Sistema verifica automaticamente
8. **Simule pagamento**: Use webhook ou atualize status manualmente
9. **Resultado**: Página atualiza automaticamente com sucesso

### 2. Teste com Webhook

```bash
# Simular webhook do Asaas
curl -X POST http://localhost:8000/webhook/asaas \
  -H "Content-Type: application/json" \
  -d '{
    "event": "PAYMENT_RECEIVED",
    "payment": {
      "id": "pay_123456789",
      "status": "RECEIVED"
    }
  }'
```

### 3. Logs de Debug

Verifique os logs para acompanhar o processo:

```bash
tail -f storage/logs/laravel.log | grep -E "(PIX|payment|webhook)"
```

## Vantagens da Nova Implementação

### 1. **Experiência do Usuário**
- ✅ Usuário permanece na mesma página
- ✅ Não precisa navegar entre páginas
- ✅ Feedback visual imediato
- ✅ Animações suaves de transição

### 2. **Performance**
- ✅ Menos requisições de página
- ✅ Verificação otimizada (3 segundos)
- ✅ Cache inteligente do webhook
- ✅ Timeout automático (30 minutos)

### 3. **Manutenibilidade**
- ✅ Código centralizado no checkout
- ✅ Reutiliza método existente
- ✅ Logs detalhados para debug
- ✅ Tratamento de erros robusto

## Estrutura do Response JSON

```json
{
  "success": true,
  "status": "RECEIVED",
  "status_text": "Pago",
  "status_changed": true,
  "is_paid": true,
  "should_redirect": true,
  "webhook_processed": true,
  "webhook_approved": false,
  "api_checked": false
}
```

## Configurações Importantes

### 1. Intervalo de Verificação
```javascript
// Verificar a cada 3 segundos
setInterval(checkPaymentStatus, 3000);
```

### 2. Timeout
```javascript
// Parar após 30 minutos
setTimeout(clearInterval, 30 * 60 * 1000);
```

### 3. Cache do Webhook
```php
// Cache por 15 minutos
Cache::put("payment_approved_{$payment->id}", $cacheData, 15 * 60);
```

## Troubleshooting

### 1. Verificação não funciona
- Verifique se a rota está registrada
- Confirme se o JavaScript está carregando
- Verifique logs do navegador (F12)

### 2. Status não atualiza
- Verifique webhook do Asaas
- Confirme configuração do cache
- Teste método `checkPaymentStatus` diretamente

### 3. Interface não atualiza
- Verifique se jQuery está carregado
- Confirme se SweetAlert está disponível
- Teste função `showPaymentSuccess` no console

## Monitoramento

### 1. Logs Importantes
```bash
# Verificação de status
grep "Verificando status do pagamento" storage/logs/laravel.log

# Cache do webhook
grep "Cache de aprovação encontrado" storage/logs/laravel.log

# Erros de verificação
grep "Erro ao verificar status" storage/logs/laravel.log
```

### 2. Métricas
- Tempo médio de detecção de pagamento
- Taxa de sucesso da verificação
- Uso do cache vs API Asaas

## Próximos Passos

1. **Otimizações**:
   - Implementar WebSockets para tempo real
   - Adicionar notificações push
   - Melhorar animações da interface

2. **Monitoramento**:
   - Dashboard de pagamentos em tempo real
   - Alertas para falhas de verificação
   - Métricas de performance

3. **Testes**:
   - Testes automatizados E2E
   - Testes de carga da verificação
   - Testes de diferentes cenários de pagamento