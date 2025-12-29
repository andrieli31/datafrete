#!/bin/bash

echo "Iniciando DataFrete com Docker..."

# Verifica se o Docker está rodando
if ! docker info > /dev/null 2>&1; then
    echo "Docker não está rodando. Por favor, inicie o Docker Desktop."
    exit 1
fi

# Copia .env.docker para .env se não existir
if [ ! -f .env ]; then
    echo "Criando arquivo .env..."
    cp .env.docker .env
fi

# Instala dependências do Composer
echo "Instalando dependências do Composer..."
docker-compose run --rm php composer install

# Executa migrations
echo "Executando migrations do banco de dados..."
docker-compose run --rm php vendor/bin/phinx migrate

echo "Configuração concluída!"
echo ""
echo "Acesse: http://localhost:8080/frontend/"
echo "phpMyAdmin: http://localhost:8081"
echo ""
echo "Para iniciar os containers: docker-compose up -d"
echo "Para parar os containers: docker-compose down"

