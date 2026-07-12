<?php
// Auto-prepend: garante que as classes core estejam carregadas
// antes de qualquer script PHP (blindagem para o Nginx + FPM)
require_once '/var/www/html/app/config/config.php';
require_once '/var/www/html/app/config/database.php';
require_once '/var/www/html/app/helpers/helpers.php';
require_once '/var/www/html/app/middlewares/Router.php';
