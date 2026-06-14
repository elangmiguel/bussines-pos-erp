@echo off
rem Start the full BusinessCmd web server stack: php-cgi FastCGI + nginx.
setlocal
set "BIN_DIR=%~dp0"

call "%BIN_DIR%php-fcgi-start.bat"
if errorlevel 1 (
    echo Failed to start php-cgi.
    exit /b 1
)

rem Brief pause so php-cgi is bound on :9000 before nginx forwards traffic.
ping -n 2 127.0.0.1 >nul

call "%BIN_DIR%nginx-start.bat"
if errorlevel 1 (
    echo Failed to start nginx. Stopping php-cgi.
    call "%BIN_DIR%php-fcgi-stop.bat"
    exit /b 1
)

echo.
echo BusinessCmd is up at http://localhost/
echo Stop with: bin\server-stop.bat
exit /b 0
