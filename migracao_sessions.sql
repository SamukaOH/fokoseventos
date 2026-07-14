-- Tabela de sessões (persistem entre deploys na Railway)
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` VARCHAR(128) NOT NULL PRIMARY KEY,
  `data` MEDIUMTEXT NOT NULL,
  `expires` INT UNSIGNED NOT NULL,
  INDEX `idx_expires` (`expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
