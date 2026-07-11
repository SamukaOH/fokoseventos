-- ============================================================
-- FOKOS EVENTOS - Sistema ERP Operacional
-- Banco de Dados MySQL
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE DATABASE IF NOT EXISTS `fokos_eventos` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `fokos_eventos`;

-- ============================================================
-- TABELA: usuarios
-- ============================================================
CREATE TABLE `usuarios` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(120) NOT NULL,
  `email` VARCHAR(180) NOT NULL UNIQUE,
  `senha` VARCHAR(255) NOT NULL,
  `tipo` ENUM('admin','motorista') NOT NULL DEFAULT 'motorista',
  `telefone` VARCHAR(20),
  `foto` VARCHAR(255),
  `status` ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `token_reset` VARCHAR(100),
  `token_expira` DATETIME,
  `ultimo_login` DATETIME,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: motoristas
-- ============================================================
CREATE TABLE `motoristas` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT UNSIGNED NOT NULL,
  `cpf` VARCHAR(14) UNIQUE,
  `cnh` VARCHAR(20),
  `veiculo` VARCHAR(80),
  `placa` VARCHAR(10),
  `disponivel` TINYINT(1) DEFAULT 1,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: clientes
-- ============================================================
CREATE TABLE `clientes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(120) NOT NULL,
  `email` VARCHAR(180),
  `telefone` VARCHAR(20),
  `whatsapp` VARCHAR(20),
  `cpf_cnpj` VARCHAR(20),
  `endereco` TEXT,
  `observacoes` TEXT,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: categorias_estoque
-- ============================================================
CREATE TABLE `categorias_estoque` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(80) NOT NULL,
  `cor` VARCHAR(7) DEFAULT '#FFCC00'
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: estoque
-- ============================================================
CREATE TABLE `estoque` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(120) NOT NULL,
  `sku` VARCHAR(60) UNIQUE,
  `categoria_id` INT UNSIGNED,
  `quantidade` INT NOT NULL DEFAULT 0,
  `quantidade_minima` INT NOT NULL DEFAULT 1,
  `custo` DECIMAL(10,2) DEFAULT 0.00,
  `fornecedor` VARCHAR(120),
  `localizacao` VARCHAR(80),
  `status` ENUM('ativo','inativo') DEFAULT 'ativo',
  `observacoes` TEXT,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`categoria_id`) REFERENCES `categorias_estoque`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: movimentacoes_estoque
-- ============================================================
CREATE TABLE `movimentacoes_estoque` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `produto_id` INT UNSIGNED NOT NULL,
  `tipo` ENUM('entrada','saida') NOT NULL,
  `quantidade` INT NOT NULL,
  `motivo` VARCHAR(200),
  `usuario_id` INT UNSIGNED,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`produto_id`) REFERENCES `estoque`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: demandas
-- ============================================================
CREATE TABLE `demandas` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(200) NOT NULL,
  `cliente_id` INT UNSIGNED,
  `responsavel` VARCHAR(120),
  `telefone` VARCHAR(20),
  `whatsapp` VARCHAR(20),
  `endereco` TEXT,
  `data_evento` DATE,
  `horario` TIME,
  `status` ENUM('pendente','preparacao','em_rota','entregue','montado','finalizado','cancelado') DEFAULT 'pendente',
  `prioridade` ENUM('baixa','media','alta','urgente') DEFAULT 'media',
  `observacoes` TEXT,
  `observacoes_internas` TEXT,
  `motorista_id` INT UNSIGNED,
  `criado_por` INT UNSIGNED,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`motorista_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`criado_por`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: demanda_materiais
-- ============================================================
CREATE TABLE `demanda_materiais` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `demanda_id` INT UNSIGNED NOT NULL,
  `produto_id` INT UNSIGNED NOT NULL,
  `quantidade` INT NOT NULL DEFAULT 1,
  FOREIGN KEY (`demanda_id`) REFERENCES `demandas`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`produto_id`) REFERENCES `estoque`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: demanda_fotos
-- ============================================================
CREATE TABLE `demanda_fotos` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `demanda_id` INT UNSIGNED NOT NULL,
  `arquivo` VARCHAR(255) NOT NULL,
  `descricao` VARCHAR(200),
  `enviado_por` INT UNSIGNED,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`demanda_id`) REFERENCES `demandas`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`enviado_por`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: demanda_tags
-- ============================================================
CREATE TABLE `demanda_tags` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `demanda_id` INT UNSIGNED NOT NULL,
  `tag` VARCHAR(60) NOT NULL,
  FOREIGN KEY (`demanda_id`) REFERENCES `demandas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: financeiro
-- ============================================================
CREATE TABLE `financeiro` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tipo` ENUM('receita','despesa') NOT NULL,
  `descricao` VARCHAR(200) NOT NULL,
  `valor` DECIMAL(12,2) NOT NULL,
  `categoria` VARCHAR(80),
  `status` ENUM('pendente','pago','cancelado') DEFAULT 'pendente',
  `vencimento` DATE,
  `pago_em` DATE,
  `demanda_id` INT UNSIGNED,
  `observacoes` TEXT,
  `criado_por` INT UNSIGNED,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`demanda_id`) REFERENCES `demandas`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`criado_por`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: notificacoes
-- ============================================================
CREATE TABLE `notificacoes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT UNSIGNED,
  `titulo` VARCHAR(120) NOT NULL,
  `mensagem` TEXT NOT NULL,
  `tipo` ENUM('info','sucesso','aviso','erro') DEFAULT 'info',
  `lida` TINYINT(1) DEFAULT 0,
  `link` VARCHAR(255),
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABELA: logs_atividade
-- ============================================================
CREATE TABLE `logs_atividade` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT UNSIGNED,
  `acao` VARCHAR(200) NOT NULL,
  `modulo` VARCHAR(60),
  `referencia_id` INT UNSIGNED,
  `ip` VARCHAR(45),
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- DADOS INICIAIS
-- ============================================================

-- Admin padrão (senha: Admin@2024)
INSERT INTO `usuarios` (`nome`, `email`, `senha`, `tipo`, `status`) VALUES
('Administrador Fokos', 'admin@fokos.com', '$2y$12$3zclSQoJYwoLPmntswjxmuyJR7G1QbdCOGRBrq3cWzUjl2XZKSBhO', 'admin', 'ativo');

-- Motorista demo (senha: Motor@2024)
INSERT INTO `usuarios` (`nome`, `email`, `senha`, `tipo`, `status`) VALUES
('João Motorista', 'motorista@fokos.com', '$2y$12$3zclSQoJYwoLPmntswjxmuyJR7G1QbdCOGRBrq3cWzUjl2XZKSBhO', 'motorista', 'ativo');

INSERT INTO `motoristas` (`usuario_id`, `cpf`, `veiculo`, `placa`) VALUES (2, '123.456.789-00', 'Fiat Ducato', 'ABC-1234');

-- Categorias padrão
INSERT INTO `categorias_estoque` (`nome`, `cor`) VALUES
('Letreiros LED', '#FFCC00'), ('Estruturas', '#00BFFF'), ('Cabos e Fixação', '#FF6B35'),
('Ferramentas', '#9B59B6'), ('Iluminação', '#2ECC71'), ('Outros', '#95A5A6');

-- Clientes demo
INSERT INTO `clientes` (`nome`, `telefone`, `whatsapp`) VALUES
('Empresa Alpha Ltda', '(11) 99999-0001', '(11) 99999-0001'),
('Festas da Maria', '(11) 99999-0002', '(11) 99999-0002'),
('Hotel Grand Plaza', '(11) 99999-0003', '(11) 99999-0003');

-- Produtos demo
INSERT INTO `estoque` (`nome`, `sku`, `categoria_id`, `quantidade`, `quantidade_minima`, `custo`) VALUES
('Letreiro LED 3D - Grande', 'LED-3D-G', 1, 8, 2, 450.00),
('Letreiro LED 3D - Médio', 'LED-3D-M', 1, 12, 3, 280.00),
('Estrutura Treliça Q30 2m', 'TREL-Q30-2', 2, 20, 5, 85.00),
('Cabo Elétrico 2x2.5', 'CAB-EL-25', 3, 150, 30, 4.50),
('Fita LED RGB 5m', 'FIT-RGB-5', 5, 35, 10, 35.00),
('Parafuso Borboleta M8', 'PAR-M8', 3, 200, 50, 0.85);

-- Demandas demo
INSERT INTO `demandas` (`titulo`, `cliente_id`, `responsavel`, `telefone`, `whatsapp`, `endereco`, `data_evento`, `horario`, `status`, `prioridade`, `criado_por`) VALUES
('Casamento Silva - Letreiro AMOR', 1, 'Ana Silva', '(11) 98888-0001', '(11) 98888-0001', 'Rua das Flores, 100 - SP', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '18:00:00', 'preparacao', 'alta', 1),
('Evento Corporativo Alpha', 2, 'Carlos Souza', '(11) 98888-0002', '(11) 98888-0002', 'Av. Paulista, 1500 - SP', DATE_ADD(CURDATE(), INTERVAL 5 DAY), '08:00:00', 'pendente', 'media', 1),
('Festa 15 Anos Maria', 3, 'Julia Mendes', '(11) 98888-0003', '(11) 98888-0003', 'Rua do Bosque, 55 - SP', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '20:00:00', 'em_rota', 'urgente', 1);

-- Financeiro demo
INSERT INTO `financeiro` (`tipo`, `descricao`, `valor`, `categoria`, `status`, `vencimento`, `criado_por`) VALUES
('receita', 'Casamento Silva - Letreiro', 2800.00, 'Locação', 'pendente', DATE_ADD(CURDATE(), INTERVAL 3 DAY), 1),
('receita', 'Evento Corporativo Alpha', 5500.00, 'Montagem', 'pendente', DATE_ADD(CURDATE(), INTERVAL 5 DAY), 1),
('despesa', 'Manutenção Veículo Ducato', 380.00, 'Manutenção', 'pago', CURDATE(), 1),
('despesa', 'Compra LED RGB', 875.00, 'Estoque', 'pendente', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 1);

-- Índices de performance
CREATE INDEX idx_demandas_status ON demandas(status);
CREATE INDEX idx_demandas_data ON demandas(data_evento);
CREATE INDEX idx_demandas_motorista ON demandas(motorista_id);
CREATE INDEX idx_financeiro_tipo ON financeiro(tipo);
CREATE INDEX idx_financeiro_status ON financeiro(status);
CREATE INDEX idx_logs_usuario ON logs_atividade(usuario_id);
CREATE INDEX idx_notif_usuario ON notificacoes(usuario_id, lida);

-- ============================================================
-- LETREIROS — Estoque por letra/símbolo/tamanho/tipo
-- ============================================================

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

-- Itens de letreiro usados em cada demanda
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

-- Dados padrão: tipos
INSERT INTO `letreiros_tipos` (`nome`, `descricao`, `cor`) VALUES
('Caixa Fechada', 'Letreiro caixa fechada sem iluminação', '#64d2ff'),
('Caixa Aberta c/ Lâmpadas', 'Letreiro caixa aberta com lâmpadas decorativas', '#FFD600'),
('Metal', 'Letreiro em metal escovado ou pintado', '#a0a0a0');

-- Dados padrão: tamanhos
INSERT INTO `letreiros_tamanhos` (`nome`, `altura_cm`) VALUES
('70cm', 70),
('1m', 100),
('1,70m', 170);
