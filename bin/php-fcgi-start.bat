@echo off
rem Start php-cgi.exe as a FastCGI listener on 127.0.0.1:9000.
rem Single-process: handles one PHP request at a time. For higher concurrency
rem launch additional instances on different ports and add an upstream pool.
setlocal

tasklist /FI "IMAGENAME eq php-cgi.exe" 2>nul | find /I "php-cgi.exe" >nul
if not errorlevel 1 (
    echo php-cgi.exe is already running. Stop it first with bin\php-fcgi-stop.bat.
    exit /b 1
)

echo Starting php-cgi FastCGI on 127.0.0.1:9000...
start "" /B php-cgi.exe -b 127.0.0.1:9000
exit /b 0
