<div align="center">

# Task Manager

**Gerenciador de tarefas multiusuário com autenticação segura, desenvolvido em PHP puro.**

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-PDO-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/docs/Web/JavaScript)
[![Git](https://img.shields.io/badge/Git-GitHub-181717?style=for-the-badge&logo=github&logoColor=white)](https://github.com/)

[**🔗 Ver demonstração online**](https://task-manager-php.infinityfree.io)

</div>

---

## Sobre o projeto

O **Task Manager** é uma aplicação web para organizar tarefas pessoais ou de trabalho. Cada usuário cria sua conta, acessa um painel privado e controla o que está pendente e o que já foi concluído, com prioridades e indicadores de progresso.

O projeto foi construído **sem frameworks**, para demonstrar domínio dos fundamentos do back-end: autenticação, sessões, acesso a banco de dados com PDO, validação de dados e práticas de segurança aplicadas de verdade, e não apenas listadas.

### O que ele resolve

- Centraliza as tarefas em um painel claro, com resumo e barra de progresso.
- Mantém os dados de cada usuário **isolados**: ninguém acessa as tarefas de outra pessoa.
- Oferece uma interface limpa, responsiva e pensada primeiro para o celular.

## Demonstração

| | |
|---|---|
| **Aplicação no ar** | [Task Manager](https://task-manager-php.infinityfree.io) |
| **Hospedagem** | InfinityFree (nuvem gratuita), com HTTPS ativo |
| **Acesso** | O cadastro é livre. Clique em **Cadastre-se** e crie uma conta de teste. |

## Principais funcionalidades

- **Autenticação completa:** cadastro, login e logout com sessões PHP.
- **Painel protegido:** páginas privadas exigem login.
- **CRUD de tarefas:** criar, listar, editar e excluir, com confirmação antes de apagar.
- **Prioridades:** alta, média e baixa, com ordenação automática.
- **Status em um clique:** alternar entre pendente e concluída.
- **Filtros e indicadores:** total, pendentes, concluídas e porcentagem de progresso.
- **Feedback ao usuário:** mensagens de sucesso e erro após cada ação.
- **Interface corporativa e responsiva:** layout em tons de azul, branco e cinza, comfortável no celular.

## Segurança

A segurança foi tratada como requisito, não como detalhe:

| Ameaça | Proteção aplicada |
|---|---|
| **SQL Injection** | Todas as consultas usam *prepared statements* do PDO |
| **Senhas vazadas** | `password_hash()` no cadastro e `password_verify()` no login; nenhuma senha é salva em texto puro |
| **Session fixation** | `session_regenerate_id(true)` a cada login |
| **Roubo de sessão** | Cookie com `HttpOnly`, `SameSite=Lax` e `Secure` em HTTPS |
| **CSRF** | Token único por sessão em todos os formulários `POST`, validado com `hash_equals()` |
| **XSS** | Toda saída dinâmica é escapada com `htmlspecialchars()` |
| **Acesso indevido a dados** | Toda consulta filtra pelo `user_id` da sessão |
| **Tentativas de login em excesso** | Após 3 senhas erradas, o login é bloqueado por alguns minutos (contador na sessão) |
| **Enumeração de contas** | Mensagem de login genérica, que não revela se o e-mail existe |
| **Vazamento de credenciais** | `config/database.php` fora do repositório via `.gitignore` |

A validação dos formulários acontece no navegador **e** no servidor.

## Tecnologias utilizadas

| Camada | Tecnologia |
|---|---|
| **Back-end** | PHP 8+ (sem frameworks) |
| **Banco de dados** | MySQL com PDO |
| **Front-end** | HTML5, CSS3 (variáveis CSS) e Bootstrap 5.3 |
| **Interatividade** | JavaScript puro (validação dos formulários e botão de mostrar a senha) |
| **Ícones e fonte** | Bootstrap Icons e Open Sans |
| **Versionamento** | Git e GitHub |
| **Hospedagem** | InfinityFree |

## Desenvolvido e publicado pelo celular

Este projeto foi **inteiramente desenvolvido, versionado e publicado a partir de um smartphone**: código, banco de dados, deploy na InfinityFree e repositório no GitHub, sem depender de um computador.

Isso exigiu organização, atenção aos detalhes e autonomia para resolver problemas de ambiente, e é parte importante da minha forma de trabalhar.

## Estrutura do projeto

```
task-manager/
├── assets/
│   ├── css/style.css            # Tema visual do projeto
│   └── js/script.js             # Validação dos formulários e botão de senha
├── config/
│   └── database.example.php     # Modelo da conexão (copie para database.php)
├── includes/
│   ├── auth.php                 # Sessão, CSRF, flash messages e proteção de rotas
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
  nome                    user_id (FK → usuarios.id, ON DELETE CASCADE)
  email (UNIQUE)          titulo
  senha_hash              descricao
  data_criacao            prioridade (baixa | media | alta)
                          status (pendente | concluida)
                          data_criacao
```

## ⚙️ Instalação e execução local

### Pré-requisitos

- [XAMPP](https://www.apachefriends.org/) (ou Laragon/WAMP), com **PHP 8+**, Apache e MySQL
- [Git](https://git-scm.com/)

### Passo a passo

**1. Clone o repositório** dentro da pasta `htdocs` do XAMPP:

```bash
cd C:/xampp/htdocs
git clone https://github.com/cauancarvalh0/task-manager.git
```

**2. Inicie o Apache e o MySQL** no painel do XAMPP.

**3. Crie o banco de dados.** Abra `http://localhost/phpmyadmin`, clique em **Novo** e crie um banco chamado `db_tarefas` com collation `utf8mb4_unicode_ci`.

**4. Importe as tabelas.** Selecione o banco `db_tarefas`, vá em **Importar** e envie o arquivo `database.sql`.

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

> ⚠️ O arquivo `config/database.php` contém credenciais e **nunca deve ser enviado ao GitHub**. Ele já está protegido pelo `.gitignore`.

## ☁️ Publicação na InfinityFree

1. Em **MySQL Databases**, crie um banco e anote host, nome, usuário e senha.
2. No **phpMyAdmin** desse banco, importe o `database.sql`.
3. Envie os arquivos para a pasta `htdocs` da hospedagem.
4. Crie `htdocs/config/database.php` com os dados do passo 1.
5. Remova o arquivo de boas-vindas da hospedagem (`index2.html`), se existir.
6. Para forçar HTTPS, crie um `.htaccess` dentro de `htdocs`:

```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## 🗺️ Próximos passos

- [ ] Limite de tentativas por e-mail/IP salvo no banco (hoje o contador fica na sessão)
- [ ] Recuperação de senha por e-mail
- [ ] Data de vencimento e lembretes
- [ ] Busca e paginação de tarefas
- [ ] Categorias e etiquetas
- [ ] Testes automatizados

## Autor

<div align="center">

**Cauan Carvalho**
Desenvolvedor web em formação · Caxias, MA

[![GitHub](https://img.shields.io/badge/GitHub-cauancarvalh0-181717?style=for-the-badge&logo=github&logoColor=white)](https://github.com/cauancarvalh0)
[![LinkedIn](https://img.shields.io/badge/LinkedIn-cauancarvalho0-0A66C2?style=for-the-badge&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/cauancarvalho0)
[![Portfólio](https://img.shields.io/badge/Portf%C3%B3lio-cauancarvalho.vercel.app-111111?style=for-the-badge&logo=vercel&logoColor=white)](https://cauancarvalho.vercel.app)
[![E-mail](https://img.shields.io/badge/E--mail-cauancarvalho004%40gmail.com-D14836?style=for-the-badge&logo=gmail&logoColor=white)](mailto:cauancarvalho004@gmail.com)

*Projeto desenvolvido para estudo e portfólio.*

</div>
