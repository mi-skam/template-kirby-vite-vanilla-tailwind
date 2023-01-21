#!/usr/bin/env bash

if [ -f .env ]
then
  . ./.env
  php -S $VITE_DEV_HOST:$VITE_DEV_PORT -t public server.php
else
  # fallback: start npm script php-server
  npm run php-server-fallback
fi
