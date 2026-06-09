CREATE TABLE usuarios (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nome      VARCHAR(150) NOT NULL,
    email     VARCHAR(150) NOT NULL UNIQUE,
    senha     VARCHAR(255) NOT NULL,
    perfil    ENUM('admin', 'aluno', 'atendente') NOT NULL DEFAULT 'atendente',
    status    ENUM('ativo', 'inativo')            NOT NULL DEFAULT 'ativo',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pessoas (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nome      VARCHAR(150) NOT NULL,
    documento VARCHAR(30)  NOT NULL,
    email     VARCHAR(150),
    telefone  VARCHAR(20),
    curso     VARCHAR(100),
    periodo   VARCHAR(20),
    status    ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_pessoa_documento (documento)
);

CREATE TABLE tipos_atendimentos (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nome      VARCHAR(100) NOT NULL,
    descricao VARCHAR(255),
    status    ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE atendimentos (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    data                DATE NOT NULL,
    hora                TIME NOT NULL,
    descricao           TEXT,
    observacao_final    TEXT,
    status              ENUM('aberto', 'em_andamento', 'concluido', 'cancelado') NOT NULL DEFAULT 'aberto',
    usuario_id          INT NOT NULL,
    pessoa_id           INT NOT NULL,
    tipo_atendimento_id INT NOT NULL,
    criado_em           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_atend_usuario
        FOREIGN KEY (usuario_id)          REFERENCES usuarios(id),
    CONSTRAINT fk_atend_pessoa
        FOREIGN KEY (pessoa_id)           REFERENCES pessoas(id),
    CONSTRAINT fk_atend_tipo
        FOREIGN KEY (tipo_atendimento_id) REFERENCES tipos_atendimentos(id),
    INDEX idx_atend_data (data),
    INDEX idx_atend_status (status)
);

CREATE TABLE relatorios_atendimentos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    periodo_inicial DATE NOT NULL,
    periodo_final   DATE NOT NULL,
    status          ENUM('gerado', 'cancelado') NOT NULL DEFAULT 'gerado',
    usuario_id      INT NOT NULL,
    criado_em       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_relatorio_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

INSERT INTO usuarios (nome, email, senha, perfil, status)
VALUES (
    'Administrador',
    'admin@atendelab.com',
    '$2y$10$fSmC8tc8agucY5b7QJ1B9ufPsgOVTzuWFLC0lDUkbkHp1kcAqPlWK',
    'admin',
    'ativo'
);
