@echo off
rem Run `composer ...` against application/composer.json.
rem Usage: bin\composer.bat install
pushd "%~dp0..\application" >nul
call composer %*
set "EXITCODE=%ERRORLEVEL%"
popd >nul
exit /b %EXITCODE%
