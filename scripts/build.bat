@echo off
cd /d "%~dp0..\application"

echo Building production assets...
npm run build

echo.
echo Done. Serve with: php artisan serve
