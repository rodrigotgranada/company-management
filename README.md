# Company Management API

## Descrição

O projeto **Company Management API** é um sistema desenvolvido utilizando o Symfony Framework, com o objetivo de realizar o gerenciamento de empresas e seus sócios. Ele foi projetado como uma API REST, permitindo a criação, leitura, atualização e exclusão de dados de empresas e sócios. Além disso, a API suporta a autenticação e permissões para diferentes tipos de usuários.

## Tecnologias Utilizadas

- **Symfony Framework** (7.1)
- **PostgreSQL** como banco de dados
- **Doctrine ORM** para manipulação do banco de dados
- **JWT (Json Web Token)** para autenticação
- **LexikJWTAuthenticationBundle** para gestão de tokens JWT

## Requisitos

- **PHP** >= 8.2
- **Composer**
- **PostgreSQL**

## Configuração do Projeto

### 1. Clonar o Repositório

```bash
git clone https://github.com/SEU_USUARIO/company-management.git
```

### 2. Instalar Dependências

Dentro do diretório do projeto, execute o comando:

```bash
composer install
```

### 3. Configurar o Banco de Dados

Edite o arquivo `.env` para configurar a conexão com o banco de dados PostgreSQL:

```
DATABASE_URL="postgresql://db_user:db_password@127.0.0.1:5432/company"
```

Crie o banco de dados:

```bash
php bin/console doctrine:database:create
```

Execute as migrações para criar as tabelas necessárias:

```bash
php bin/console doctrine:migrations:migrate
```

### 4. Gerar Chaves JWT

Para gerar as chaves JWT, execute os seguintes comandos:

```bash
mkdir -p config/jwt
openssl genrsa -out config/jwt/private.pem -aes256 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

Certifique-se de configurar as variáveis de ambiente no arquivo `.env`:

```
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=YOUR_PASSPHRASE
```

### 5. Executar o Servidor de Desenvolvimento

Para iniciar o servidor de desenvolvimento, execute:

```bash
php bin/console server:run
```

A API estará disponível em `http://127.0.0.1:8000`.

## Documentação da API

A API possui as seguintes rotas principais:

### Autenticação

- **POST /api/register**: Registro de um novo usuário.
- **POST /api/login**: Login do usuário para obter o token JWT.
- **POST /api/logout**: Logout do usuário.

### Empresas

- **GET /api/empresas**: Listar todas as empresas.
- **POST /api/empresas**: Criar uma nova empresa (somente ADMIN).
- **PUT /api/empresas/{id}**: Atualizar uma empresa existente (somente ADMIN).
- **DELETE /api/empresas/{id}**: Excluir uma empresa (somente ADMIN).
- **GET /api/empresas/{id}/socios**: Listar todos os sócios de uma empresa.

### Sócios

- **POST /api/socios**: Criar um novo sócio (somente ADMIN).
- **GET /api/socios**: Listar todos os sócios.

### Usuários

- **GET /api/users**: Listar todos os usuários (somente ADMIN).
- **GET /api/users/me**: Visualizar informações do próprio usuário.

## Roles e Permissionamento

- **ROLE_USER**: Pode acessar rotas de leitura (listar empresas, listar sócios, etc.).
- **ROLE_ADMIN**: Pode acessar todas as rotas, incluindo criação, atualização e exclusão de empresas e sócios.

## Testando a API

Para testar a API, recomendo utilizar o **Postman** ou outra ferramenta similar. Você pode importar o arquivo de configuração do Postman fornecido neste repositório para facilitar os testes.
