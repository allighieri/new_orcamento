<?php

// Conectar diretamente ao banco MySQL
$host = 'localhost';
$dbname = 'sistem_orcamento';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== INVESTIGAÇÃO DO PROBLEMA ESPECÍFICO ===\n\n";
    
    // Investigar Payment 23
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = 23");
    $stmt->execute();
    $payment23 = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "PAYMENT 23 (Anual):";
    echo "\n- ID: {$payment23['id']}";
    echo "\n- Company ID: {$payment23['company_id']}";
    echo "\n- Billing Cycle: {$payment23['billing_cycle']}";
    echo "\n- Asaas Payment ID: " . ($payment23['asaas_payment_id'] ?: 'NULL');
    echo "\n- Asaas Subscription ID: " . ($payment23['asaas_subscription_id'] ?: 'NULL');
    echo "\n- Status: {$payment23['status']}";
    echo "\n- Created: {$payment23['created_at']}";
    echo "\n\n";
    
    // Investigar Subscription 13
    $stmt2 = $pdo->prepare("SELECT * FROM subscriptions WHERE asaas_subscription_id = ?");
    $stmt2->execute([$payment23['asaas_subscription_id']]);
    $subscription13 = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    if ($subscription13) {
        echo "SUBSCRIPTION COM MESMO ASAAS_SUBSCRIPTION_ID:";
        echo "\n- ID: {$subscription13['id']}";
        echo "\n- Company ID: {$subscription13['company_id']}";
        echo "\n- Billing Cycle: {$subscription13['billing_cycle']}";
        echo "\n- Status: {$subscription13['status']}";
        echo "\n- Asaas Subscription ID: " . ($subscription13['asaas_subscription_id'] ?: 'NULL');
        echo "\n- Created: {$subscription13['created_at']}";
        echo "\n\n";
        
        echo "🚨 PROBLEMA IDENTIFICADO:\n";
        echo "- Payment 23 é ANUAL mas Subscription 13 é MENSAL\n";
        echo "- Ambos têm o mesmo asaas_subscription_id: {$payment23['asaas_subscription_id']}\n";
        echo "- Payment criado em: {$payment23['created_at']}\n";
        echo "- Subscription criada em: {$subscription13['created_at']}\n\n";
        
        if ($payment23['created_at'] < $subscription13['created_at']) {
            echo "📅 Payment foi criado ANTES da Subscription\n";
            echo "💡 CAUSA PROVÁVEL: O webhook do payment anual criou uma subscription mensal por engano\n\n";
        } else {
            echo "📅 Subscription foi criada ANTES do Payment\n";
            echo "💡 CAUSA PROVÁVEL: Subscription mensal foi criada primeiro, depois payment anual reutilizou o ID\n\n";
        }
    }
    
    echo "=== VERIFICAÇÃO DO WEBHOOK CONTROLLER ===\n\n";
    
    // Simular o que aconteceria no webhook
    echo "Simulando processamento do webhook para Payment 23:\n";
    echo "1. Payment tem asaas_subscription_id: {$payment23['asaas_subscription_id']}\n";
    echo "2. Buscar subscription ativa da empresa {$payment23['company_id']}...\n";
    
    $activeSubStmt = $pdo->prepare("SELECT id, billing_cycle, status FROM subscriptions WHERE company_id = ? AND status = 'active'");
    $activeSubStmt->execute([$payment23['company_id']]);
    $activeSubs = $activeSubStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "3. Subscriptions ativas encontradas: " . count($activeSubs) . "\n";
    foreach ($activeSubs as $sub) {
        echo "   - ID {$sub['id']}: {$sub['billing_cycle']} | {$sub['status']}\n";
    }
    
    if (count($activeSubs) > 0) {
        echo "4. ✅ Subscription ativa encontrada - webhook retorna sem criar nova subscription\n";
        echo "5. 🚨 ESTE É O PROBLEMA: Payment anual não cria subscription anual porque já existe subscription ativa\n\n";
    } else {
        echo "4. ❌ Nenhuma subscription ativa - webhook deveria criar nova subscription\n\n";
    }
    
    echo "=== SOLUÇÃO PROPOSTA ===\n\n";
    echo "O problema está na lógica do WebhookController:\n";
    echo "- Quando um payment anual chega via webhook, ele verifica se existe subscription ativa\n";
    echo "- Se existe, ele assume que é um pagamento recorrente e não cria nova subscription\n";
    echo "- Mas deveria verificar se a subscription ativa tem o mesmo billing_cycle\n";
    echo "- Se o payment é anual mas a subscription ativa é mensal, deveria criar nova subscription anual\n\n";
    
    echo "CORREÇÃO NECESSÁRIA no WebhookController:\n";
    echo "- Linha ~207: Verificar não apenas se existe subscription ativa, mas se é do mesmo tipo\n";
    echo "- Se payment é anual e subscription ativa é mensal, cancelar a mensal e criar anual\n";
    
} catch (PDOException $e) {
    echo "Erro de conexão: " . $e->getMessage() . "\n";
}