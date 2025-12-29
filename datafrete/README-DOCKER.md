# 🐳 DataFrete - Instalação com Docker

Este guia explica como executar o projeto DataFrete usando Docker com PHP 8.2.

## 📋 Pré-requisitos

- Docker Desktop instalado e rodando
- Docker Compose (vem com Docker Desktop)

## 🚀 Início Rápido

### 1. Execute o script de inicialização

**Windows:**
```bash
docker-start.bat
```

**Linux/Mac:**
```bash
chmod +x docker-start.sh
./docker-start.sh
```

### 2. Inicie os containers

```bash
docker-compose up -d
```

### 3. Acesse a aplicação

- **Frontend:** http://localhost:8080/frontend/
- **API:** http://localhost:8080/api/distances
- **phpMyAdmin:** http://localhost:8081

## 📝 Comandos Úteis

### Iniciar containers
```bash
docker-compose up -d
```

### Parar containers
```bash
docker-compose down
```

### Ver logs
```bash
docker-compose logs -f
```

### Executar comandos no container PHP
```bash
docker-compose exec php bash
```

### Executar migrations
```bash
docker-compose exec php vendor/bin/phinx migrate
```

### Instalar dependências
```bash
docker-compose exec php composer install
```

## 🔧 Configuração

### Variáveis de Ambiente

As variáveis de ambiente estão configuradas no `docker-compose.yml`:

- `DB_HOST=mysql`
- `DB_PORT=3306`
- `DB_NAME=datafrete`
- `DB_USER=root`
- `DB_PASS=root`

Para alterar, edite o arquivo `docker-compose.yml` ou crie um arquivo `.env`.

### Portas

- **8080:** Apache/PHP (Frontend e API)
- **3306:** MySQL
- **8081:** phpMyAdmin

Para alterar as portas, edite o arquivo `docker-compose.yml`.

## 🗄️ Banco de Dados

O banco de dados é criado automaticamente quando o container MySQL inicia.

### Acessar via phpMyAdmin

1. Acesse: http://localhost:8081
2. Usuário: `root`
3. Senha: `root`

### Acessar via linha de comando

```bash
docker-compose exec mysql mysql -uroot -proot datafrete
```

## 🐛 Troubleshooting

### Erro: "Port already in use"

Alguma porta (8080, 3306, 8081) já está em uso. Altere as portas no `docker-compose.yml`.

### Erro: "Cannot connect to database"

Aguarde alguns segundos para o MySQL inicializar completamente. Verifique os logs:

```bash
docker-compose logs mysql
```

### Limpar tudo e recomeçar

```bash
docker-compose down -v
docker-compose up -d --build
```

## 📦 Estrutura Docker

```
datafrete/
├── docker/
│   ├── php/
│   │   └── php.ini          # Configurações PHP
│   └── mysql/
│       └── init.sql         # Script de inicialização do banco
├── docker-compose.yml       # Configuração dos containers
├── Dockerfile               # Imagem PHP customizada
└── .dockerignore            # Arquivos ignorados no build
```

## 🔄 Atualizar o Projeto

```bash
# Parar containers
docker-compose down

# Atualizar código
git pull

# Reconstruir e iniciar
docker-compose up -d --build
```

