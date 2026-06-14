@echo off
cd /d "%~dp0..\application"

echo WARNING: This will drop all tables and re-run all migrations + seeders.
set /p confirm=Are you sure? (y/N):
if /i not "%confirm%"=="y" (
    echo Aborted.
    exit /b 0
)

echo.
echo Running fresh migration with seed...
php artisan migrate:fresh --seed

echo.
echo Done.
