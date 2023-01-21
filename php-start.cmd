@echo off
setlocal EnableDelayedExpansion

if exist "%~dp0.env" (
    for /F "tokens=1,2 delims==" %%G IN (.env) DO set "%%G=%%H"
    php -S !VITE_DEV_HOST!:!VITE_DEV_PORT! -t public server.php
) else (
    npm run php-server-fallback
)