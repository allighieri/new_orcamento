#!/bin/sh

# Variáveis de caminho
REPO_DIR="/home/karaoke/repositories/new_orcamento"
DEST_DIR="/home/karaoke/public_html/orcamento"

echo "Iniciando deploy..."

# Navega até o diretório do repositório e faz o pull mais recente
cd $REPO_DIR
git pull origin main

# Instala as dependências do Composer e do NPM
echo "Instalando dependências..."
/opt/cpanel/ea-php81/root/usr/bin/php /home/karaoke/public_html/orcamento/composer.phar install --no-dev --optimize-autoloader
npm install
npm run build

# Copia os arquivos da subpasta para o diretório de destino
echo "Copiando arquivos para o diretório de produção..."
rsync -av --exclude='.env' $REPO_DIR/sistem_orcamento/ $DEST_DIR/

# Executa as migrações e limpa o cache do Laravel
echo "Executando comandos do Laravel..."
cd $DEST_DIR
php artisan migrate --force
php artisan cache:clear
php artisan config:clear
php artisan route:clear

echo "Deploy concluído com sucesso!"