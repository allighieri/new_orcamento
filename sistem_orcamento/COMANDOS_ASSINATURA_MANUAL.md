# Comandos para Gerenciamento Manual de Assinaturas

Este documento explica como criar e gerenciar assinaturas manualmente sem depender do Asaas ou webhooks.

## Comandos Disponíveis

### 1. Listar Empresas e Planos

```bash
php artisan subscription:list-data
```

Este comando mostra:
- Todas as empresas cadastradas e seus status de assinatura
- Todos os planos disponíveis com preços e limites
- Cálculo de economia anual por plano

**Opções:**
- `--companies`: Listar apenas empresas
- `--plans`: Listar apenas planos
- `--all`: Listar tudo (padrão)

### 2. Criar Assinatura Manual

```bash
php artisan subscription:create-manual {company_id} {plan_id} [opções]
```

**Parâmetros obrigatórios:**
- `company_id`: ID da empresa
- `plan_id`: ID do plano

**Opções disponíveis:**
- `--cycle=monthly|yearly`: Ciclo de cobrança (padrão: monthly)
- `--status=pending|active|cancelled`: Status da assinatura (padrão: active)
- `--start-date=YYYY-MM-DD`: Data de início (padrão: hoje)
- `--end-date=YYYY-MM-DD`: Data de fim (calculada automaticamente se não informada)
- `--auto-renew=true|false`: Renovação automática (padrão: true)

**Exemplos:**
```bash
# Criar assinatura ativa mensal
php artisan subscription:create-manual 1 2

# Criar assinatura anual
php artisan subscription:create-manual 1 3 --cycle=yearly

# Criar assinatura pendente com data específica
php artisan subscription:create-manual 1 2 --status=pending --start-date=2024-01-01

# Criar assinatura sem renovação automática
php artisan subscription:create-manual 1 2 --auto-renew=false
```

### 3. Ativar Assinatura

```bash
php artisan subscription:activate {subscription_id}
```

Ativa uma assinatura que está com status 'pending'. Automaticamente:
- Altera o status para 'active'
- Define a data de início como agora
- Cria o controle de uso para o mês atual
- Verifica se não há conflito com outras assinaturas ativas

### 4. Cancelar Assinatura

```bash
php artisan subscription:cancel {subscription_id} [--reason="motivo"]
```

Cancela uma assinatura ativa. Opções:
- `--reason`: Motivo do cancelamento (opcional)

**Exemplo:**
```bash
php artisan subscription:cancel 1 --reason="Solicitação do cliente"
```

### 5. Verificar Assinaturas

```bash
php artisan check:subscriptions [company_id]
```

- Sem parâmetro: Lista as últimas 10 assinaturas
- Com company_id: Lista todas as assinaturas da empresa específica

## Fluxo de Trabalho Recomendado

### 1. Consultar Dados Disponíveis
```bash
php artisan subscription:list-data
```

### 2. Criar Assinatura
```bash
# Para assinatura imediata
php artisan subscription:create-manual {company_id} {plan_id} --status=active

# Para assinatura futura
php artisan subscription:create-manual {company_id} {plan_id} --status=pending --start-date=2024-01-01
```

### 3. Verificar Criação
```bash
php artisan check:subscriptions {company_id}
```

### 4. Ativar se Necessário
```bash
# Apenas se criou com status=pending
php artisan subscription:activate {subscription_id}
```

## Validações e Regras

### Criação de Assinatura
- ✅ Empresa deve existir
- ✅ Plano deve existir e estar ativo
- ✅ Não pode haver duas assinaturas ativas para a mesma empresa
- ✅ Ciclo deve ser 'monthly' ou 'yearly'
- ✅ Status deve ser 'pending', 'active', 'cancelled' ou 'expired'
- ✅ Datas são validadas automaticamente

### Ativação de Assinatura
- ✅ Assinatura deve existir
- ✅ Não pode estar já ativa
- ✅ Empresa não pode ter outra assinatura ativa
- ✅ Cria controle de uso automaticamente

### Cancelamento de Assinatura
- ✅ Assinatura deve existir
- ✅ Solicita confirmação antes de cancelar
- ✅ Mantém período de graça ativo
- ✅ Registra motivo do cancelamento

## Características Importantes

### Independência do Asaas
- ❌ Não cria clientes no Asaas
- ❌ Não gera cobranças automáticas
- ❌ Não depende de webhooks
- ✅ Funciona completamente offline
- ✅ Controle total sobre datas e status

### Controle de Uso
- ✅ Criado automaticamente para assinaturas ativas
- ✅ Respeita limites do plano
- ✅ Funciona com planos ilimitados

### Logs e Auditoria
- ✅ Todas as operações são logadas
- ✅ Histórico completo de alterações
- ✅ Identificação de operações manuais

## Casos de Uso

### 1. Migração de Sistema Antigo
```bash
# Criar assinaturas para clientes existentes
php artisan subscription:create-manual 1 2 --start-date=2024-01-01 --end-date=2024-12-31
```

### 2. Teste de Funcionalidades
```bash
# Criar assinatura de teste
php artisan subscription:create-manual 1 1 --status=pending
php artisan subscription:activate 1
```

### 3. Correção de Problemas
```bash
# Cancelar assinatura problemática
php artisan subscription:cancel 1 --reason="Problema técnico"

# Criar nova assinatura correta
php artisan subscription:create-manual 1 2 --status=active
```

### 4. Assinaturas Promocionais
```bash
# Criar assinatura com período específico
php artisan subscription:create-manual 1 3 --start-date=2024-01-01 --end-date=2024-03-31 --auto-renew=false
```

## Troubleshooting

### Erro: "Empresa já possui uma assinatura ativa"
**Solução:** Cancele a assinatura ativa primeiro ou use status 'pending'
```bash
php artisan subscription:cancel {subscription_id_ativa}
php artisan subscription:create-manual {company_id} {plan_id}
```

### Erro: "Empresa com ID X não encontrada"
**Solução:** Verifique se a empresa existe
```bash
php artisan subscription:list-data --companies
```

### Erro: "Plano com ID X não encontrado"
**Solução:** Verifique se o plano existe e está ativo
```bash
php artisan subscription:list-data --plans
```

## Monitoramento

### Verificar Status Geral
```bash
# Ver últimas assinaturas criadas
php artisan check:subscriptions

# Ver dados completos
php artisan subscription:list-data
```

### Logs do Sistema
Todas as operações são registradas nos logs do Laravel:
```bash
tail -f storage/logs/laravel.log | grep "assinatura"
```

---

**Nota:** Estes comandos foram criados para permitir o gerenciamento completo de assinaturas sem depender de serviços externos como Asaas ou sistemas de webhook. Eles são ideais para migrações, testes, correções e situações onde você precisa de controle total sobre o processo de assinatura.