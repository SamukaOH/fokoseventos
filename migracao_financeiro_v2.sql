USE fokos_eventos;

-- Tipos extras para neon (só insere se não existir)
INSERT IGNORE INTO letreiros_tipos (nome,descricao,cor) VALUES
('Caixa Fechada Neon','Caixa fechada com neon','#ff6fff'),
('Caixa Fechada Neon LED','Caixa fechada com neon LED','#00ffff');

-- Preços por tipo+tamanho
CREATE TABLE IF NOT EXISTS letreiros_precos (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao      VARCHAR(200) NOT NULL,
  tipo_id        INT UNSIGNED NOT NULL,
  tamanho_id     INT UNSIGNED NOT NULL,
  preco_unitario DECIMAL(10,2) NOT NULL DEFAULT 0,
  ativo          TINYINT(1) DEFAULT 1,
  UNIQUE KEY uk_tp (tipo_id, tamanho_id)
) ENGINE=InnoDB;

-- Reformular financeiro
ALTER TABLE financeiro
  ADD COLUMN IF NOT EXISTS demanda_id       INT UNSIGNED NULL           AFTER id,
  ADD COLUMN IF NOT EXISTS subtotal         DECIMAL(10,2) DEFAULT 0     AFTER valor,
  ADD COLUMN IF NOT EXISTS desconto_tipo    ENUM('valor','percentual')  AFTER subtotal,
  ADD COLUMN IF NOT EXISTS desconto_valor   DECIMAL(10,2) DEFAULT 0     AFTER desconto_tipo,
  ADD COLUMN IF NOT EXISTS frete            DECIMAL(10,2) DEFAULT 0     AFTER desconto_valor,
  ADD COLUMN IF NOT EXISTS valor_motorista  DECIMAL(10,2) DEFAULT 0     AFTER frete,
  ADD COLUMN IF NOT EXISTS orcamento_auto   TINYINT(1) DEFAULT 0        AFTER valor_motorista,
  ADD COLUMN IF NOT EXISTS data_lancamento  DATE NULL                   AFTER orcamento_auto,
  ADD COLUMN IF NOT EXISTS criado_por       INT UNSIGNED NULL           AFTER data_lancamento;

-- Data de retirada na demanda
ALTER TABLE demandas
  ADD COLUMN IF NOT EXISTS data_retirada DATE NULL AFTER horario_retirada;
