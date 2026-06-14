@echo off
setlocal

rem ------------------------------------------------------------
rem Resolve absolute project paths (NO ".." allowed)
rem ------------------------------------------------------------

set "ROOT_DIR=%~dp0.."
for %%i in ("%ROOT_DIR%") do set "ROOT_DIR=%%~fi"

set "NGINX_DIR=%ROOT_DIR%\web server"

set "TEMPLATE=%NGINX_DIR%\conf\businesscmd.conf.template"
set "CONF_FILE=%NGINX_DIR%\conf\businesscmd.conf"

rem Convert to forward slashes for nginx
set "NGINX_ROOT=%ROOT_DIR:\=/%"

rem ------------------------------------------------------------
rem Prevent duplicate nginx instances
rem ------------------------------------------------------------

tasklist /FI "IMAGENAME eq nginx.exe" 2>nul | find /I "nginx.exe" >nul
if not errorlevel 1 (
    echo nginx is already running.
    exit /b 1
)

echo Generating nginx businesscmd.conf...

rem ------------------------------------------------------------
rem Generate config from template
rem ------------------------------------------------------------

powershell -NoProfile -Command ^
    "(Get-Content '%TEMPLATE%') -replace '__ROOT__', '%NGINX_ROOT%' | Set-Content '%CONF_FILE%'"

rem ------------------------------------------------------------
rem Test config
rem ------------------------------------------------------------

pushd "%NGINX_DIR%" >nul

nginx.exe -t
if errorlevel 1 (
    popd >nul
    exit /b 1
)

echo Starting nginx...
start "" /B nginx.exe

set "EXITCODE=%ERRORLEVEL%"

popd >nul
exit /b %EXITCODE%