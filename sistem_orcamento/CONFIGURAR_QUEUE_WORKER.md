# Configuração do Worker de Fila para Processamento Automático de Webhooks

## Problema Identificado

O sistema estava configurado para usar filas (`QUEUE_CONNECTION=database`) mas não havia nenhum worker rodando para processar os jobs automaticamente. Isso causava:

- Jobs de webhook ficavam pendentes na tabela `jobs`
- Pagamentos não eram processados automaticamente
- Assinaturas não eram criadas/atualizadas
- Controle de uso não era configurado

## Solução Implementada

### 1. Worker Manual (Desenvolvimento)

Para desenvolvimento local, execute:

```bash
php artisan queue:work --tries=3 --timeout=120
```

### 2. Configuração para Produção

#### Opção A: Supervisor (Recomendado)

Crie o arquivo `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/karaoke/public_html/orcamento_test/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=karaoke
numprocs=2
redirect_stderr=true
stdout_logfile=/home/karaoke/public_html/orcamento_test/storage/logs/worker.log
stopwaitsecs=3600
```

Depois execute:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

#### Opção B: Cron Job

Adicione ao crontab:

```bash
* * * * * cd /home/karaoke/public_html/orcamento_test && php artisan queue:work --stop-when-empty --max-jobs=1000 >> /dev/null 2>&1
```

#### Opção C: Systemd Service

Crie o arquivo `/etc/systemd/system/laravel-queue.service`:

```ini
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
User=karaoke
Group=karaoke
Restart=always
ExecStart=/opt/cpanel/ea-php83/root/usr/bin/php /home/karaoke/public_html/orcamento_test/artisan queue:work --sleep=3 --tries=3 --max-time=3600
WorkingDirectory=/home/karaoke/public_html/orcamento_test

[Install]
WantedBy=multi-user.target
```

Depois execute:
```bash
sudo systemctl enable laravel-queue
sudo systemctl start laravel-queue
```

### 3. Monitoramento

Para verificar se o sistema está funcionando:

```bash
php check_queue_status.php
```

### 4. Comandos Úteis

```bash
# Verificar jobs na fila
php artisan queue:monitor

# Processar jobs pendentes uma vez
php artisan queue:work --once

# Limpar jobs falhados
php artisan queue:flush

# Reiniciar workers (após deploy)
php artisan queue:restart
```

## Configurações Importantes

### .env
```
QUEUE_CONNECTION=database
DB_QUEUE_TABLE=jobs
DB_QUEUE=default
DB_QUEUE_RETRY_AFTER=90
```

### Timeout do Job
O job `ProcessAsaasWebhook` está configurado com:
- `$tries = 3` (3 tentativas)
- `$timeout = 120` (120 segundos de timeout)

## Verificação de Funcionamento

1. **Jobs Pendentes**: Deve ser 0
2. **Jobs Falhados**: Deve ser 0 ou mínimo
3. **Pagamentos**: Status RECEIVED deve criar subscription automaticamente
4. **Controle de Uso**: Deve ser criado com budgets_limit correto

## Troubleshooting

### Worker não está processando
```bash
# Verificar se há workers rodando
ps aux | grep "queue:work"

# Verificar logs
tail -f storage/logs/laravel.log
```

### Jobs ficam falhando
```bash
# Ver jobs falhados
php artisan queue:failed

# Reprocessar job específico
php artisan queue:retry {id}

# Reprocessar todos os jobs falhados
php artisan queue:retry all
```

### Performance
- Use `--sleep=3` para reduzir uso de CPU
- Use `--max-jobs=1000` para reiniciar worker periodicamente
- Use `--max-time=3600` para reiniciar worker a cada hora
- Configure múltiplos workers com `numprocs=2` no Supervisor

## Importante

⚠️ **Nunca faça correções manuais no banco de dados**. O sistema deve processar os webhooks automaticamente através da fila.

✅ **Sempre mantenha um worker rodando** para garantir que os webhooks sejam processados em tempo real.