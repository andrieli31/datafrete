@echo off
echo Iniciando DataFrete com Docker...

REM Verifica se o Docker está rodando
docker info >nul 2>&1
if errorlevel 1 (
    echo Docker não está rodando. Por favor, inicie o Docker Desktop.
    pause
    exit /b 1
)

REM Copia .env.docker para .env se não existir
if not exist .env (
    echo Criando arquivo .env...
    copy .env.docker .env
)

REM Instala dependências do Composer
echo Instalando dependências do Composer...
docker-compose run --rm php composer install

REM Executa migrations
echo Executando migrations do banco de dados...
docker-compose run --rm php vendor/bin/phinx migrate

echo Configuração concluída!
echo.
echo Acesse: http://localhost:8080/frontend/
echo phpMyAdmin: http://localhost:8081
echo.
echo Para iniciar os containers: docker-compose up -d
echo Para parar os containers: docker-compose down
pause

