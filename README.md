# Task Manager

Sistema web de gerenciamento de tarefas com cadastro e login de usuários, desenvolvido em **PHP puro** (sem frameworks), **MySQL com PDO** e **Bootstrap 5**. Cada usuário tem seu próprio painel e enxerga somente as tarefas que criou.

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-PDO-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E?logo=javascript&logoColor=black)](https://developer.mozilla.org/docs/Web/JavaScript)

**🔗 Demonstração online:** [COLE-AQUI-O-LINK-DO-SEU-SITE](https://COLE-AQUI-O-LINK-DO-SEU-SITE)
*(o cadastro é livre: crie uma conta de teste na tela "Cadastre-se")*

---

## Sobre o projeto

O objetivo é demonstrar, de ponta a ponta, os fundamentos de uma aplicação web back-end: autenticação com sessões, criptografia de senhas, CRUD relacional, isolamento de dados por usuário e boas práticas de segurança, em uma estrutura simples e organizada. A interface segue um visual corporativo, limpo e responsivo (mobile-first).

## Funcionalidades

- Cadastro de usuários com validação no cliente e no servidor
- Login e logout com sessões PHP
- Painel protegido por autenticação
- Resumo com total de tarefas, pendentes, concluídas e barra de progresso
- Criar, editar, listar e excluir tarefas
- Alternar status (pendente / concluída) com um clique
- Prioridade alta, média e baixa, com ordenação automática
- Filtro por status (todas, pendentes, concluídas)
- Confirmação em modal antes de excluir
- Mensagens de feedback (flash) após cada ação
- Layout responsivo, pensado primeiro para o celular

## Tecnologias

| Tecnologia | Uso |
|---|---|
| **PHP 8+** | Back-end, sessões e regras de negócio |
| **MySQL + PDO** | Banco de dados com prepared statements |
| **HTML5 / CSS3** | Estrutura e estilo (tema próprio com variáveis CSS) |
| **Bootstrap 5.3** | Grid, componentes e responsividade |
| **Bootstrap Icons** | Ícones |
| **JavaScript (vanilla)** | Validações, modal de confirmação e pequenas melhorias de UX |
| **Git / GitHub** | Versionamento |
| **InfinityFree** | Hospedagem da demonstração |

## Segurança aplicada

- **SQL Injection:** todas as consultas usam *prepared statements* do PDO.
- **Senhas:** `password_hash()` no cadastro e `password_verify()` no login. Nenhuma senha é salva em texto puro.
- **XSS:** toda saída dinâmica passa por `htmlspecialchars()` (função `e()`).
- **CSRF:** token por sessão em todos os formulários `POST`, validado com `hash_equals()`.
- **Session fixation:** `session_regenerate_id(true)` após o login.
- **Cookie de sessão:** `HttpOnly`, `SameSite=Lax` e `Secure` quando o site usa HTTPS.
- **Autorização:** toda leitura, edição e exclusão filtra por `user_id` da sessão. Um usuário nunca acessa tarefas de outro.
- **Rotas privadas:** `exigir_login()` bloqueia o acesso sem sessão; o logout só funciona via `POST` com token.
- **Mensagens de login genéricas:** não revelam se o e-mail existe.
- **Cabeçalhos HTTP:** `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` e `Cache-Control`.
- **Credenciais fora do repositório:** `config/database.php` está no `.gitignore`.

## Estrutura de pastas

```
task-manager/
├── assets/
│   ├── css/style.css            # Tema visual (branco, preto e bege)
│   └── js/validation.js         # Validações e interações em JS puro
├── config/
│   └── database.example.php     # Modelo da conexão PDO (copie para database.php)
├── includes/
│   ├── auth.php                 # Sessão, CSRF, flash e proteção de rotas
│   ├── header.php               # Cabeçalho e navegação
│   └── footer.php               # Rodapé, modal e scripts
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
├── database.sql                 # Criação das tabelas
├── .gitignore
└── README.md
```

## Modelo de dados

```
usuarios (1) ───────< (N) tarefas
  id (PK)                 id (PK)
  nome                    user_id (FK → usuarios.id, ON DELETE CASCADE)
  email (UNIQUE)          titulo
  senha_hash              descricao
  data_criacao            prioridade (baixa | media | alta)
                          status (pendente | concluida)
                          data_criacao
```

## Como executar localmente

**Pré-requisitos:** PHP 8+, MySQL/MariaDB e um servidor local (XAMPP, Laragon ou similar).

1. **Clone o repositório** dentro da pasta pública do servidor (`htdocs` no XAMPP):
   ```bash
   git clone https://github.com/cauancarvalh0/task-manager.git
   ```
2. **Crie o banco de dados** no phpMyAdmin (por exemplo, `db_tarefas`, com collation `utf8mb4_unicode_ci`).
3. **Importe as tabelas:** selecione o banco, abra a aba *Importar* e envie o arquivo `database.sql`.
4. **Configure a conexão:** copie `config/database.example.php` para `config/database.php` e preencha `host`, `banco`, `usuario` e `senha`.
5. **Acesse** `http://localhost/task-manager` e crie sua conta.

## Como publicar no InfinityFree

1. No painel da InfinityFree, crie um banco em **MySQL Databases** e anote host, nome, usuário e senha.
2. Abra o **phpMyAdmin** do banco criado e importe o `database.sql`.
3. Envie os arquivos do projeto para a pasta `htdocs` (gerenciador de arquivos ou FTP).
4. Crie manualmente `htdocs/config/database.php` com os dados do passo 1.
5. Apague o arquivo de boas-vindas da hospedagem (`index2.html`), se existir.
6. Para forçar HTTPS, crie um `.htaccess` dentro de `htdocs` com:
   ```apache
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

## Melhorias futuras

- [ ] Limite de tentativas de login (proteção contra força bruta)
- [ ] Recuperação de senha por e-mail
- [ ] Data de vencimento e lembretes
- [ ] Busca e paginação de tarefas
- [ ] Categorias ou etiquetas
- [ ] Testes automatizados

## Autor

**Cauan Carvalho**, desenvolvedor web em formação, de Caxias, MA.

- GitHub: [@cauancarvalh0](https://github.com/cauancarvalh0)
- LinkedIn: [cauancarvalho0](https://www.linkedin.com/in/cauancarvalho0)
- Portfólio: [cauancarvalho.vercel.app](https://cauancarvalho.vercel.app)
- E-mail: cauancarvalho004@gmail.com

---

Projeto desenvolvido para fins de estudo e portfólio.
