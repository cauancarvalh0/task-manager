-- Gerenciador de Tarefas - criação das tabelas
-- Importe com o banco JÁ selecionado (phpMyAdmin > banco > Importar).

-- Tabela de usuários: a senha é armazenada apenas como hash (password_hash)
CREATE TABLE IF NOT EXISTS usuarios (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome         VARCHAR(100) NOT NULL,
    email        VARCHAR(150) NOT NULL,
    senha_hash   VARCHAR(255) NOT NULL,
    data_criacao DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    -- Índice único impede dois cadastros com o mesmo e-mail
    UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de tarefas: cada tarefa pertence a um único usuário
CREATE TABLE IF NOT EXISTS tarefas (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id      INT UNSIGNED NOT NULL,
    titulo       VARCHAR(150) NOT NULL,
    descricao    TEXT NULL,
    prioridade   ENUM('baixa', 'media', 'alta') NOT NULL DEFAULT 'media',
    status       ENUM('pendente', 'concluida')  NOT NULL DEFAULT 'pendente',
    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_tarefas_user (user_id),
    -- ON DELETE CASCADE: ao excluir um usuário, suas tarefas também são removidas
    CONSTRAINT fk_tarefas_usuario
        FOREIGN KEY (user_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
