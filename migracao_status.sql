USE fokos_eventos;

-- Atualizar enum de status
ALTER TABLE `demandas` MODIFY COLUMN `status`
  ENUM('pendente','preparacao','em_rota','entregue','em_retirada','devolvido','finalizado','cancelado')
  DEFAULT 'pendente';

-- Adicionar horário de retirada nas demandas
ALTER TABLE `demandas` ADD COLUMN IF NOT EXISTS `horario_retirada` TIME NULL AFTER `horario`;
