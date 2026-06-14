@echo off
rem Gracefully stop nginx. Falls back to taskkill if the master process is gone.
setlocal
set "NGINX_DIR=%~dp0..\web server"

pushd "%NGINX_DIR%" >nul
echo Stopping nginx...
nginx.exe -s quit 2>nul
if errorlevel 1 (
    echo nginx.exe -s quit failed; force-killing remaining nginx processes.
    taskkill /F /IM nginx.exe >nul 2>&1
)
popd >nul
exit /b 0
