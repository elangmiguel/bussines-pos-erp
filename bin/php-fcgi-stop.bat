@echo off
rem Stop all php-cgi.exe FastCGI listener processes.
echo Stopping php-cgi FastCGI listener(s)...
taskkill /F /IM php-cgi.exe >nul 2>&1
exit /b 0
