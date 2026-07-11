-- ============================================================
-- MIGRAÇÃO: Adicionar tabelas de letreiros ao banco existente
-- Execute este arquivo no phpMyAdmin se já tiver o banco criado
-- ============================================================

USE fokos_eventos;

CREATE TABLE IF NOT EXISTS `letreiros_tipos` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(80) NOT NULL,
  `descricao` VARCHAR(200),
  `cor` VARCHAR(7) DEFAULT '#FFD600',
  `ativo` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `letreiros_tamanhos` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(40) NOT NULL,
  `altura_cm` INT,
  `ativo` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `letreiros_estoque` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `caractere` VARCHAR(10) NOT NULL,
  `tipo_id` INT UNSIGNED NOT NULL,
  `tamanho_id` INT UNSIGNED NOT NULL,
  `quantidade_total` INT NOT NULL DEFAULT 0,
  `quantidade_disponivel` INT NOT NULL DEFAULT 0,
  `quantidade_reservada` INT NOT NULL DEFAULT 0,
  `quantidade_rua` INT NOT NULL DEFAULT 0,
  `observacoes` TEXT,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_caractere_tipo_tamanho` (`caractere`, `tipo_id`, `tamanho_id`),
  FOREIGN KEY (`tipo_id`) REFERENCES `letreiros_tipos`(`id`),
  FOREIGN KEY (`tamanho_id`) REFERENCES `letreiros_tamanhos`(`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `demanda_letreiros` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `demanda_id` INT UNSIGNED NOT NULL,
  `letreiro_id` INT UNSIGNED NOT NULL,
  `caractere` VARCHAR(10) NOT NULL,
  `quantidade` INT NOT NULL DEFAULT 1,
  `tipo_id` INT UNSIGNED NOT NULL,
  `tamanho_id` INT UNSIGNED NOT NULL,
  `status` ENUM('reservado','na_rua','devolvido') DEFAULT 'reservado',
  FOREIGN KEY (`demanda_id`) REFERENCES `demandas`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`letreiro_id`) REFERENCES `letreiros_estoque`(`id`)
) ENGINE=InnoDB;

-- Adicionar coluna em_retirada ao enum de status das demandas
ALTER TABLE `demandas` MODIFY COLUMN `status` 
  ENUM('pendente','preparacao','em_rota','em_retirada','entregue','montado','finalizado','cancelado') 
  DEFAULT 'pendente';

-- Dados padrão: tipos
INSERT IGNORE INTO `letreiros_tipos` (`id`, `nome`, `descricao`, `cor`) VALUES
(1, 'Caixa Fechada', 'Letreiro caixa fechada sem iluminação', '#64d2ff'),
(2, 'Caixa Aberta c/ Lâmpadas', 'Letreiro caixa aberta com lâmpadas decorativas', '#FFD600'),
(3, 'Metal', 'Letreiro em metal escovado ou pintado', '#a0a0a0');

-- Dados padrão: tamanhos
INSERT IGNORE INTO `letreiros_tamanhos` (`id`, `nome`, `altura_cm`) VALUES
(1, '70cm', 70),
(2, '1m', 100),
(3, '1,70m', 170);

-- Adicionar motorista de retirada nas demandas
ALTER TABLE `demandas` 
  ADD COLUMN IF NOT EXISTS `motorista_retirada_id` INT UNSIGNED NULL AFTER `motorista_id`,
  ADD FOREIGN KEY IF NOT EXISTS fk_mot_ret (`motorista_retirada_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL;
