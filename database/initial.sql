-- ============================================
-- Schema: Sistema de Atendimento (MySQL 8+)
-- ============================================

CREATE TABLE Usuario (
     id      INT AUTO_INCREMENT PRIMARY KEY,
     nome    VARCHAR(150) NOT NULL,
     email   VARCHAR(150) NOT NULL UNIQUE,
     senha   VARCHAR(255) NOT NULL,
     perfil  VARCHAR(50)  NOT NULL,
     status  VARCHAR(20)  NOT NULL DEFAULT 'ATIVO'
) ;

CREATE TABLE PessoaAtendida (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nome      VARCHAR(150) NOT NULL,
    documento VARCHAR(30)  NOT NULL,
    email     VARCHAR(150),
    telefone  VARCHAR(20),
    curso     VARCHAR(100),
    periodo   VARCHAR(20),
    status    VARCHAR(20)  NOT NULL DEFAULT 'ATIVO',
    UNIQUE KEY uk_pessoa_documento (documento)
) ;

CREATE TABLE TipoAtendimento (
     id        INT AUTO_INCREMENT PRIMARY KEY,
     nome      VARCHAR(100) NOT NULL,
     descricao VARCHAR(255),
     status    VARCHAR(20)  NOT NULL DEFAULT 'ATIVO'
) ;

CREATE TABLE Atendimento (
         id                  INT AUTO_INCREMENT PRIMARY KEY,
         data                DATE NOT NULL,
         hora                TIME NOT NULL,
         descricao           TEXT,
         observacaoFinal     TEXT,
         status              VARCHAR(20) NOT NULL DEFAULT 'ABERTO',
         usuario_id          INT NOT NULL,
         pessoa_atendida_id  INT NOT NULL,
         tipo_atendimento_id INT NOT NULL,
         CONSTRAINT fk_atend_usuario
             FOREIGN KEY (usuario_id)          REFERENCES Usuario(id),
         CONSTRAINT fk_atend_pessoa
             FOREIGN KEY (pessoa_atendida_id)  REFERENCES PessoaAtendida(id),
         CONSTRAINT fk_atend_tipo
             FOREIGN KEY (tipo_atendimento_id) REFERENCES TipoAtendimento(id),
         INDEX idx_atend_data (data),
         INDEX idx_atend_status (status)
) ;

CREATE TABLE RelatorioAtendimento (
      id             INT AUTO_INCREMENT PRIMARY KEY,
      periodoInicial DATE NOT NULL,
      periodoFinal   DATE NOT NULL,
      status         VARCHAR(20) NOT NULL DEFAULT 'GERADO',
      usuario_id     INT NOT NULL,
      CONSTRAINT fk_relatorio_usuario
          FOREIGN KEY (usuario_id) REFERENCES Usuario(id)
) ;

INSERT INTO usuarios (nome, email, senha, perfil, status)
VALUES (
 'Administrador',
 'admin@atendelab.com',
 '$2y$10$J9P2kU2BAMZ3TZcuxTsW4e1D/lka8EocYHzvyoOZmCNcWDQz3RuVC',
 'admin',
 'ativo'
);
