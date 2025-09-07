#!/bin/sh

# Variáveis de caminho
REPO_DIR="/home/karaoke/repositories/test_production"
DEST_DIR="/home/karaoke/public_html/orcamento_test"

echo "Iniciando deploy..."

# Navega até o diretório do repositório e faz o pull mais recente
cd $REPO_DIR
echo "Sincronizando com o GitHub..."
git pull origin main

# Copia os arquivos da pasta do projeto para a pasta de produção
echo "Copiando arquivos para o diretorio de producao..."
cp -R $REPO_DIR/sistem_orcamento/. $DEST_DIR/

# Executa as tarefas do Laravel
echo "Executando comandos do Laravel..."
cd $DEST_DIR

# Instala as dependências do Composer
echo "Instalando dependencias do Composer..."
/opt/cpanel/ea-php83/root/usr/bin/php /opt/cpanel/composer/bin/composer install --no-dev --optimize-autoloader

# Instala as dependencias do Node.js
echo "Instalando dependencias do Node.js..."
npm install

# Compila os arquivos de front-end com o Vite
echo "Compilando assets com o Vite..."
npm run build

# Executa as migrações (seguro)
echo "Rodando migrations...ok"
/opt/cpanel/ea-php83/root/usr/bin/php artisan migrate --force

# Limpa todos os caches de uma vez
echo "Limpando caches..."
/opt/cpanel/ea-php83/root/usr/bin/php artisan optimize:clear

echo "Deploy concluído com sucesso!"