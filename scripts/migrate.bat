@echo off
cd /d "%~dp0..\application"

echo Running database migrations...
php artisan migrate

echo.
echo To seed: php artisan db:seed
