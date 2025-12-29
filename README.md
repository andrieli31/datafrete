# DataFrete - Sistema de Cadastro de Distâncias entre CEPs

Sistema completo para cadastro e cálculo de distâncias entre CEPs, desenvolvido com PHP (backend) e Vue.js 2 + Bootstrap 4 (frontend).

## 📋 Requisitos

- PHP >= 7.4
- MySQL >= 5.7 ou MariaDB >= 10.2
- Composer
- Servidor web (Apache/Nginx) ou PHP built-in server
- Extensões PHP: PDO, curl, json

## 🚀 Instalação

### 1. Clone o repositório ou extraia os arquivos

```bash
cd datafrete
```

### 2. Instale as dependências do Composer

```bash
composer install
```

### 3. Configure o banco de dados

Crie um arquivo `.env` na raiz do projeto (copie do `.env.example`):

```bash
cp .env.example .env
```

Edite o arquivo `.env` com suas credenciais do banco de dados:

```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=datafrete
DB_USER=seu_usuario
DB_PASS=sua_senha
```

### 4. Crie o banco de dados

```sql
CREATE DATABASE datafrete CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Execute as migrations com Phinx

```bash
vendor/bin/phinx migrate
```

### 6. Configure o servidor web

#### Opção A: Apache (Recomendado)

Certifique-se de que o módulo `mod_rewrite` está habilitado e configure um VirtualHost apontando para a raiz do projeto.

Exemplo de configuração:

```apache
<VirtualHost *:80>
    ServerName datafrete.local
    DocumentRoot "C:/caminho/para/datafrete"
    
    <Directory "C:/caminho/para/datafrete">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Importante:** Ajuste o caminho conforme sua instalação.

#### Opção B: PHP Built-in Server (Desenvolvimento)

Abra dois terminais:

**Terminal 1 - Backend API:**
```bash
cd backend
php -S localhost:8000 -t api
```

**Terminal 2 - Frontend:**
```bash
cd frontend
php -S localhost:8080
```

**Nota:** Se usar o PHP built-in server, **edite** o arquivo `frontend/app.js` na linha 1 e altere:

```javascript
const API_BASE_URL = 'http://localhost:8000';
```

### 7. Acesse o sistema

Abra seu navegador e acesse:

- **Apache/Nginx:** `http://datafrete.local/frontend/` ou `http://localhost/datafrete/frontend/`
- **PHP Built-in:** `http://localhost:8080`

**Dica:** Para facilitar, consulte também o arquivo `INSTALACAO.md` com um guia passo a passo mais detalhado.

## 📁 Estrutura do Projeto

```
datafrete/
├── backend/
│   ├── api/
│   │   └── index.php          # Endpoint principal da API
│   ├── src/
│   │   ├── Config/
│   │   │   └── Database.php    # Configuração do banco de dados
│   │   ├── Models/
│   │   │   └── Distance.php    # Modelo de distâncias
│   │   └── Services/
│   │       ├── BrasilApiService.php    # Integração com Brasil API
│   │       ├── CacheService.php        # Sistema de cache
│   │       ├── DistanceCalculator.php  # Cálculo de distância (Haversine)
│   │       ├── CsvImporter.php         # Importação CSV
│   │       └── Logger.php              # Sistema de logs
│   ├── cache/                  # Cache de consultas (criado automaticamente)
│   ├── logs/                   # Logs do sistema (criado automaticamente)
│   └── uploads/                # Uploads temporários (criado automaticamente)
├── frontend/
│   ├── index.html              # Interface principal
│   └── app.js                  # Aplicação Vue.js
├── database/
│   └── migrations/             # Migrations do Phinx
├── composer.json
├── phinx.php                   # Configuração do Phinx
└── README.md
```

## 🔧 Funcionalidades

### ✅ Requisitos Implementados

- ✅ Persistência em banco de dados (CEP origem, CEP destino, distância, datas)
- ✅ Tela de exibição de lista de distâncias
- ✅ Opção de adicionar nova distância
- ✅ Validação de CEP através da Brasil API
- ✅ Cálculo de distância entre coordenadas (fórmula de Haversine)
- ✅ Importação em massa via arquivo CSV
- ✅ Backend em PHP
- ✅ README com instruções

### 🌟 Funcionalidades Extras (Recomendações)

- ✅ **Cache de consultas da API** - Consultas de CEP são cacheadas por 24 horas
- ✅ **Bootstrap 4** - Interface moderna e responsiva
- ✅ **VueJS 2** - Framework JavaScript reativo
- ✅ **Phinx** - Sistema de migrations
- ✅ **Limite de cálculos** - Máximo de 100 cálculos por importação para evitar bloqueio da API
- ✅ **Logs estruturados** - Sistema de logs em JSON
- ⚠️ **RabbitMQ** - Não implementado (requer instalação adicional, pode ser adicionado posteriormente)

## 📡 API Endpoints

### GET `/distances`
Lista todas as distâncias cadastradas.

**Parâmetros:**
- `page` (opcional): Número da página (padrão: 1)
- `limit` (opcional): Itens por página (padrão: 100)

**Resposta:**
```json
{
  "success": true,
  "data": [...],
  "total": 10,
  "page": 1,
  "limit": 100
}
```

### POST `/distances`
Calcula e cadastra uma nova distância.

**Body:**
```json
{
  "cep_origem": "01310100",
  "cep_destino": "04547000"
}
```

**Resposta:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "cep_origem": "01310100",
    "cep_destino": "04547000",
    "distancia": "12.45",
    "created_at": "2024-01-01 10:00:00",
    "updated_at": "2024-01-01 10:00:00"
  }
}
```

### POST `/import`
Importa distâncias em massa via arquivo CSV.

**Form Data:**
- `file`: Arquivo CSV

**Formato do CSV:**
```csv
CEP origem,CEP fim
01310100,04547000
01310100,20040020
```

**Resposta:**
```json
{
  "success": true,
  "data": {
    "success": [...],
    "errors": [...],
    "total": 2
  }
}
```

### POST `/validate-cep`
Valida um CEP através da Brasil API.

**Body:**
```json
{
  "cep": "01310100"
}
```

## 📝 Formato do CSV para Importação

O arquivo CSV deve conter as seguintes colunas:

```csv
CEP origem,CEP fim
01310100,04547000
01310100,20040020
```

**Importante:**
- Use apenas números nos CEPs (sem hífen ou pontos)
- Cada linha deve conter exatamente 2 colunas
- O limite de processamento é de 100 registros por importação

## 🔍 Cálculo de Distância

O sistema utiliza a **fórmula de Haversine** para calcular a distância entre duas coordenadas geográficas:

```
a = sin²(Δφ/2) + cos φ1 ⋅ cos φ2 ⋅ sin²(Δλ/2)
c = 2 ⋅ atan2( √a, √(1−a) )
d = R ⋅ c
```

Onde:
- φ = latitude
- λ = longitude
- R = raio da Terra (6371 km)

A distância é retornada em **quilômetros** com 2 casas decimais.

## 🗄️ Estrutura do Banco de Dados

### Tabela `distances`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT (PK) | Identificador único |
| cep_origem | VARCHAR(8) | CEP de origem |
| cep_destino | VARCHAR(8) | CEP de destino |
| distancia | DECIMAL(10,2) | Distância em km |
| created_at | DATETIME | Data de criação |
| updated_at | DATETIME | Data de atualização |

**Índice único:** `(cep_origem, cep_destino)` - Evita duplicatas

## 🐛 Troubleshooting

### Erro de conexão com banco de dados
- Verifique as credenciais no arquivo `.env`
- Certifique-se de que o MySQL está rodando
- Verifique se o banco de dados foi criado

### Erro ao validar CEP
- Verifique sua conexão com a internet
- A Brasil API pode estar temporariamente indisponível
- Verifique os logs em `backend/logs/`

### Erro 404 na API
- Verifique a configuração do `.htaccess` (Apache)
- Se usar PHP built-in server, certifique-se de usar a rota correta
- Verifique a constante `API_BASE_URL` no `frontend/app.js`

### Cache não funciona
- Verifique permissões da pasta `backend/cache/`
- Certifique-se de que o PHP tem permissão de escrita

## 📄 Licença

Este projeto foi desenvolvido como desafio técnico.

## 👨‍💻 Autor

Desenvolvido para o desafio técnico DataFrete.

