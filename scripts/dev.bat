@echo off
cd /d "%~dp0..\application"

echo Starting BusinessCmd dev environment...
echo   - Laravel server    (http://localhost:8000)
echo   - Queue listener
echo   - Reverb WebSocket  (ws://localhost:8080)
echo   - Vite              (http://localhost:5173)
echo.

composer run dev
