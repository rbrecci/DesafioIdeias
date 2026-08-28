-- AtivoLab: esquema do banco (MySQL 8 / MariaDB 10.4+)
-- Importe pelo phpMyAdmin do InfinityFree.
-- Sem CREATE DATABASE: no InfinityFree o banco ja vem criado pelo painel.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS setores (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome      VARCHAR(80)  NOT NULL,
  sigla     VARCHAR(12)  NOT NULL,
  criado_em DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_setores_sigla (sigla)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorias (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome      VARCHAR(80)  NOT NULL,
  prefixo   VARCHAR(8)   NOT NULL COMMENT 'prefixo do patrimonio, ex: NB, PROJ',
  criado_em DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categorias_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome          VARCHAR(120) NOT NULL,
  matricula     VARCHAR(30)  NOT NULL,
  email         VARCHAR(150) NOT NULL,
  senha_hash    VARCHAR(255) NOT NULL,
  papel         ENUM('admin','gestor','tecnico') NOT NULL DEFAULT 'tecnico',
  setor_id      INT UNSIGNED NULL,
  ativo         TINYINT(1)   NOT NULL DEFAULT 1,
  ultimo_acesso DATETIME     NULL,
  criado_em     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_email (email),
  UNIQUE KEY uq_usuarios_matricula (matricula),
  KEY ix_usuarios_setor (setor_id),
  CONSTRAINT fk_usuarios_setor FOREIGN KEY (setor_id) REFERENCES setores (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ativos (
  id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  patrimonio     VARCHAR(40)   NOT NULL COMMENT 'identificador impresso na etiqueta QR',
  nome           VARCHAR(140)  NOT NULL,
  categoria_id   INT UNSIGNED  NULL,
  setor_id       INT UNSIGNED  NULL,
  local_atual    VARCHAR(120)  NULL,
  numero_serie   VARCHAR(80)   NULL,
  fabricante     VARCHAR(80)   NULL,
  modelo         VARCHAR(80)   NULL,
  data_aquisicao DATE          NULL,
  valor          DECIMAL(12,2) NULL,
  status         ENUM('disponivel','em_uso','em_manutencao','emprestado','baixado') NOT NULL DEFAULT 'disponivel',
  observacoes    TEXT          NULL,
  foto           VARCHAR(140)  NULL,
  criado_por     INT UNSIGNED  NULL,
  criado_em      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_ativos_patrimonio (patrimonio),
  KEY ix_ativos_setor (setor_id),
  KEY ix_ativos_categoria (categoria_id),
  KEY ix_ativos_status (status),
  KEY ix_ativos_nome (nome),
  CONSTRAINT fk_ativos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias (id) ON DELETE SET NULL,
  CONSTRAINT fk_ativos_setor     FOREIGN KEY (setor_id)     REFERENCES setores (id)    ON DELETE SET NULL,
  CONSTRAINT fk_ativos_criador   FOREIGN KEY (criado_por)   REFERENCES usuarios (id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- append-only: nunca sofre UPDATE nem DELETE. E o historico de localizacao.
CREATE TABLE IF NOT EXISTS movimentacoes (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ativo_id         INT UNSIGNED NOT NULL,
  setor_origem_id  INT UNSIGNED NULL,
  setor_destino_id INT UNSIGNED NULL,
  local_origem     VARCHAR(120) NULL,
  local_destino    VARCHAR(120) NULL,
  responsavel_id   INT UNSIGNED NULL,
  observacao       VARCHAR(255) NULL,
  criado_em        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_mov_ativo (ativo_id, criado_em),
  CONSTRAINT fk_mov_ativo   FOREIGN KEY (ativo_id)         REFERENCES ativos (id)   ON DELETE CASCADE,
  CONSTRAINT fk_mov_origem  FOREIGN KEY (setor_origem_id)  REFERENCES setores (id)  ON DELETE SET NULL,
  CONSTRAINT fk_mov_destino FOREIGN KEY (setor_destino_id) REFERENCES setores (id)  ON DELETE SET NULL,
  CONSTRAINT fk_mov_resp    FOREIGN KEY (responsavel_id)   REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- append-only: registra toda alteracao de campo.
CREATE TABLE IF NOT EXISTS auditoria (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  entidade       VARCHAR(40)  NOT NULL,
  entidade_id    INT UNSIGNED NOT NULL,
  acao           VARCHAR(20)  NOT NULL COMMENT 'criou, alterou, movimentou, login',
  campo          VARCHAR(60)  NULL,
  valor_anterior TEXT         NULL,
  valor_novo     TEXT         NULL,
  usuario_id     INT UNSIGNED NULL,
  ip             VARCHAR(45)  NULL,
  criado_em      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_aud_entidade (entidade, entidade_id, criado_em),
  KEY ix_aud_usuario (usuario_id, criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
