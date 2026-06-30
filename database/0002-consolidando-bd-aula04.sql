USE atendelab;

-- 1) usuarios
UPDATE usuarios SET perfil = 'atendente' WHERE perfil = 'aluno';

ALTER TABLE usuarios
    MODIFY nome   VARCHAR(100) NOT NULL,
    MODIFY email  VARCHAR(100) NOT NULL,
    MODIFY perfil ENUM('admin', 'atendente') NOT NULL DEFAULT 'atendente';

ALTER TABLE usuarios
    ADD COLUMN atualizado_em TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 2) pessoas
UPDATE pessoas
   SET email = CONCAT('sem-email-', id, '@exemplo.test')
 WHERE email IS NULL OR email = '';

ALTER TABLE pessoas
    MODIFY email VARCHAR(150) NOT NULL,
    MODIFY curso VARCHAR(120),
    ADD COLUMN observacoes TEXT AFTER periodo;

ALTER TABLE pessoas
    ADD COLUMN atualizado_em TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 3) tipos_atendimentos
ALTER TABLE tipos_atendimentos
    MODIFY descricao TEXT;

ALTER TABLE tipos_atendimentos
    ADD COLUMN atualizado_em TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 4) atendimentos
ALTER TABLE atendimentos
    CHANGE data data_atendimento     DATE NOT NULL,
    CHANGE hora horario_atendimento  TIME NOT NULL;

UPDATE atendimentos
   SET descricao = 'Atendimento registrado.'
 WHERE descricao IS NULL OR descricao = '';

UPDATE atendimentos SET status = 'concluido' WHERE status = 'cancelado';

ALTER TABLE atendimentos
    MODIFY descricao TEXT NOT NULL,
    MODIFY status ENUM('aberto', 'em_andamento', 'concluido')
        NOT NULL DEFAULT 'aberto';

ALTER TABLE atendimentos
    ADD COLUMN atualizado_em TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
