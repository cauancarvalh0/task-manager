<div align="center">

# Task Manager

**Aplicação web multiusuário para gerenciamento de tarefas, com autenticação segura, desenvolvida em PHP puro.**

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-PDO-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=flat-square&logo=javascript&logoColor=black)](https://developer.mozilla.org/docs/Web/JavaScript)
[![Git](https://img.shields.io/badge/Git-GitHub-181717?style=flat-square&logo=github&logoColor=white)](https://github.com/)

[Acessar a demonstração online](https://task-manager-php.infinityfree.io)

</div>

---

## Sumário

1. [Visão geral](#visão-geral)
2. [Demonstração](#demonstração)
3. [Funcionalidades](#funcionalidades)
4. [Segurança](#segurança)
5. [Tecnologias](#tecnologias)
6. [Desenvolvimento e publicação](#desenvolvimento-e-publicação)
7. [Estrutura do projeto](#estrutura-do-projeto)
8. [Instalação e execução local](#instalação-e-execução-local)
9. [Publicação na InfinityFree](#publicação-na-infinityfree)
10. [Próximos passos](#próximos-passos)
11. [Autor](#autor)

---

## Visão geral

O **Task Manager** permite que cada usuário crie uma conta, acesse um painel privado e acompanhe suas tarefas, com prioridades, status e indicadores de progresso.

O projeto foi construído sem frameworks, com o objetivo de demonstrar domínio dos fundamentos do desenvolvimento back-end: autenticação, sessões, acesso a banco de dados com PDO, validação de dados e boas práticas de segurança.

**Problemas que o sistema resolve**

- Centraliza as tarefas em um painel claro, com resumo e barra de progresso.
- Isola os dados de cada usuário: ninguém acessa as tarefas de outra pessoa.
- Oferece uma interface limpa e responsiva, com bom uso em celulares e computadores.

## Demonstração

| Item | Informação |
|---|---|
| Aplicação online | (https://task-manager-php.infinityfree.io) |
| Hospedagem | InfinityFree, com HTTPS ativo |
| Acesso | Cadastro livre. Use a opção **Cadastre-se** para criar uma conta de teste. |

## Funcionalidades

- Cadastro de usuários com validação no navegador e no servidor.
- Login e logout com sessões PHP.
- Bloqueio temporário do login após tentativas incorretas.
- Painel protegido, acessível apenas a usuários autenticados.
- Criação, listagem, edição e exclusão de tarefas, com confirmação antes de excluir.
- Prioridades alta, média e baixa, com ordenação automática.
- Alternância de status entre pendente e concluída.
- Filtros por status e indicadores de total, pendentes, concluídas e progresso.
- Mensagens de sucesso e de erro após cada ação.
- Interface em tons de azul, branco e cinza, com cartões e selos de prioridade, responsiva e pensada primeiro para o celular.

## Segurança

| Ameaça | Proteção aplicada |
|---|---|
| SQL Injection | Todas as consultas usam prepared statements do PDO |
| Exposição de senhas | `password_hash()` no cadastro e `password_verify()` no login; nenhuma senha é salva em texto puro |
| Session fixation | `session_regenerate_id(true)` a cada login |
| Roubo de sessão | Cookie com `HttpOnly`, `SameSite=Lax` e `Secure` quando o site usa HTTPS |
| CSRF | Token único por sessão em todos os formulários POST, validado com `hash_equals()` |
| XSS | Toda saída dinâmica é escapada com `htmlspecialchars()` |
| Acesso indevido a dados | Toda consulta filtra pelo `user_id` da sessão |
| Tentativas de login em excesso | Após 3 senhas incorretas, o login é bloqueado por 5 minutos (contador na sessão) |
| Enumeração de contas | Mensagem de login genérica, que não revela se o e-mail existe |
| Vazamento de credenciais | O arquivo `config/database.php` fica fora do repositório por meio do `.gitignore` |

A validação dos formulários ocorre no navegador e, novamente, no servidor.

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Back-end | PHP 8+ (sem frameworks) |
| Banco de dados | MySQL com PDO |
| Front-end | HTML5, CSS3 (variáveis CSS) e Bootstrap 5.3 |
| Interatividade | JavaScript puro (validação dos formulários e botão de mostrar a senha) |
| Ícones e fonte | Bootstrap Icons e Open Sans |
| Versionamento | Git e GitHub |
| Hospedagem | InfinityFree |

## Desenvolvimento e publicação

O projeto foi desenvolvido, versionado e publicado a partir de um smartphone: código, banco de dados, publicação na InfinityFree e repositório no GitHub.

Esse fluxo exigiu organização, atenção aos detalhes e autonomia para resolver problemas de ambiente.

## Estrutura do projeto

```
task-manager/
├── assets/
│   ├── css/style.css            # Tema visual do projeto
│   └── js/script.js             # Validação dos formulários e botão de senha
├── config/
│   └── database.example.php     # Modelo da conexão (copie para database.php)
├── includes/
│   ├── auth.php                 # Sessão, CSRF, mensagens e proteção de rotas
│   ├── header.php               # Topo, faixa azul e navegação
│   └── footer.php               # Rodapé e scripts
├── tasks/
│   ├── create.php               # Criar tarefa
│   ├── edit.php                 # Editar tarefa
│   ├── toggle.php               # Alternar status
│   └── delete.php               # Excluir tarefa
├── dashboard.php                # Painel do usuário
├── login.php                    # Login
├── register.php                 # Cadastro
├── logout.php                   # Encerrar sessão
├── index.php                    # Redireciona para login ou painel
├── database.sql                 # Estrutura das tabelas
└── README.md
```

### Modelo de dados

```
usuarios (1) ───────< (N) tarefas
  id (PK)                 id (PK)
  nome                    user_id (FK -> usuarios.id, ON DELETE CASCADE)
  email (UNIQUE)          titulo
  senha_hash              descricao
  data_criacao            prioridade (baixa | media | alta)
                          status (pendente | concluida)
                          data_criacao
```

## Instalação e execução local

### Pré-requisitos

- [XAMPP](https://www.apachefriends.org/) (ou Laragon/WAMP), com PHP 8+, Apache e MySQL
- [Git](https://git-scm.com/)

### Passo a passo

**1. Clone o repositório** dentro da pasta `htdocs` do XAMPP:

```bash
cd C:/xampp/htdocs
git clone https://github.com/cauancarvalh0/task-manager.git
```

**2. Inicie o Apache e o MySQL** pelo painel do XAMPP.

**3. Crie o banco de dados.** Acesse `http://localhost/phpmyadmin`, clique em **Novo** e crie um banco chamado `db_tarefas`, com collation `utf8mb4_unicode_ci`.

**4. Importe as tabelas.** Selecione o banco `db_tarefas`, abra a aba **Importar** e envie o arquivo `database.sql`.

**5. Configure a conexão.** Copie o arquivo de exemplo e preencha com os seus dados:

```bash
cp config/database.example.php config/database.php
```

No XAMPP, os valores padrão costumam ser:

```php
$host    = 'localhost';
$banco   = 'db_tarefas';
$usuario = 'root';
$senha   = '';
```

**6. Acesse** `http://localhost/task-manager` e crie sua conta.

> **Importante:** o arquivo `config/database.php` contém credenciais e nunca deve ser enviado ao GitHub. Ele já está protegido pelo `.gitignore`.

## Publicação na InfinityFree

1. Em **MySQL Databases**, crie um banco e anote host, nome, usuário e senha.
2. No phpMyAdmin desse banco, importe o arquivo `database.sql`.
3. Envie os arquivos do projeto para a pasta `htdocs` da hospedagem.
4. Crie `htdocs/config/database.php` com os dados do passo 1.
5. Remova o arquivo de boas-vindas da hospedagem (`index2.html`), caso exista.
6. Para forçar HTTPS, crie um arquivo `.htaccess` dentro de `htdocs` com o conteúdo:

```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## Próximos passos

- [ ] Limite de tentativas de login por e-mail ou IP, salvo no banco (hoje o contador fica na sessão)
- [ ] Recuperação de senha por e-mail
- [ ] Data de vencimento e lembretes
- [ ] Busca e paginação de tarefas
- [ ] Categorias e etiquetas
- [ ] Testes automatizados

## Autor

**Cauan Carvalho**
Desenvolvedor web em formação, Caxias, MA

- GitHub: [github.com/cauancarvalh0](https://github.com/cauancarvalh0)
- LinkedIn: [linkedin.com/in/cauancarvalho0](https://www.linkedin.com/in/cauancarvalho0)
- Portfólio: [cauancarvalho.vercel.app](https://cauancarvalho.vercel.app)
- E-mail: [cauancarvalho004@gmail.com](mailto:cauancarvalho004@gmail.com)

---

Projeto desenvolvido para fins de estudo e portfólio.
