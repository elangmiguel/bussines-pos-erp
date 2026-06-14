@echo off
rem Stop the full BusinessCmd web server stack.
setlocal
set "BIN_DIR=%~dp0"

call "%BIN_DIR%nginx-stop.bat"
call "%BIN_DIR%php-fcgi-stop.bat"

echo BusinessCmd web server stopped.
exit /b 0
