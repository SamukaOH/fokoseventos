#!/bin/bash
set -e

# Garantir UM ÚNICO MPM (prefork) — remove event/worker se existirem
a2dismod mpm_event  2>/dev/null || true
a2dismod mpm_worker 2>/dev/null || true
a2enmod  mpm_prefork 2>/dev/null || true

# Porta dinâmica da Railway
PORT="${PORT:-8080}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
