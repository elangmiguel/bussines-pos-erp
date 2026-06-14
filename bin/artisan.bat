@echo off
rem Run `php artisan ...` from the application/ directory.
rem Usage: bin\artisan.bat migrate --force
pushd "%~dp0..\application" >nul
php artisan %*
set "EXITCODE=%ERRORLEVEL%"
popd >nul
exit /b %EXITCODE%
