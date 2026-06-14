@echo off
rem Reload nginx configuration without dropping connections.
setlocal
set "NGINX_DIR=%~dp0..\web server"

pushd "%NGINX_DIR%" >nul
echo Testing nginx config...
nginx.exe -t
if errorlevel 1 (
    popd >nul
    echo Config test failed. Reload aborted.
    exit /b 1
)
echo Reloading nginx...
nginx.exe -s reload
set "EXITCODE=%ERRORLEVEL%"
popd >nul
exit /b %EXITCODE%
