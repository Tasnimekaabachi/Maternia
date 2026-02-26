@echo off
title Maternia - CompreFace Setup
echo ========================================
echo    MATERNIA - COMPREFACE INSTALLER
echo ========================================
echo.

:: Check if running as administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERROR] This script must be run as Administrator!
    echo Right-click on this file and select "Run as administrator"
    pause
    exit /b 1
)

echo [1/6] Checking system requirements...
echo.

:: Check Windows version
ver | find "10.0" > nul
if %errorLevel% equ 0 (
    echo [OK] Windows 10/11 detected
) else (
    echo [WARNING] This script is optimized for Windows 10/11
)

:: Check if Docker is already installed
echo.
echo [2/6] Checking Docker installation...
docker --version >nul 2>&1
if %errorLevel% equ 0 (
    echo [OK] Docker is already installed
    docker --version
) else (
    echo [INFO] Docker not found. Installing Docker Desktop...
    
    :: Download Docker Desktop installer
    echo Downloading Docker Desktop...
    powershell -Command "Invoke-WebRequest -Uri 'https://desktop.docker.com/win/stable/Docker%20Desktop%20Installer.exe' -OutFile '%TEMP%\DockerDesktopInstaller.exe'"
    
    if not exist "%TEMP%\DockerDesktopInstaller.exe" (
        echo [ERROR] Failed to download Docker Desktop
        pause
        exit /b 1
    )
    
    echo Installing Docker Desktop...
    echo This may take a few minutes...
    start /wait "%TEMP%\DockerDesktopInstaller.exe" install --quiet
    
    echo [OK] Docker Desktop installed
    echo.
    echo [IMPORTANT] Please restart your computer after installation completes
    echo.
)

:: Check if Docker is running
echo.
echo [3/6] Checking if Docker is running...
docker info >nul 2>&1
if %errorLevel% equ 0 (
    echo [OK] Docker is running
) else (
    echo [WARNING] Docker is not running
    echo Starting Docker Desktop...
    start "" "C:\Program Files\Docker\Docker\Docker Desktop.exe"
    echo Waiting for Docker to start...
    timeout /t 30 /nobreak >nul
)

:: Download CompreFace
echo.
echo [4/6] Downloading CompreFace...
set COMPREFACE_VERSION=1.2.0
set COMPREFACE_ZIP=CompreFace_%COMPREFACE_VERSION%.zip
set COMPREFACE_URL=https://github.com/exadel-inc/CompreFace/releases/download/v%COMPREFACE_VERSION%/%COMPREFACE_ZIP%

if exist "%COMPREFACE_ZIP%" (
    echo [OK] CompreFace zip already downloaded
) else (
    echo Downloading CompreFace v%COMPREFACE_VERSION%...
    powershell -Command "Invoke-WebRequest -Uri '%COMPREFACE_URL%' -OutFile '%COMPREFACE_ZIP%'"
    
    if not exist "%COMPREFACE_ZIP%" (
        echo [ERROR] Failed to download CompreFace
        pause
        exit /b 1
    )
    echo [OK] Download complete
)

:: Extract CompreFace
echo.
echo [5/6] Extracting CompreFace...
if exist "CompreFace_%COMPREFACE_VERSION%" (
    echo [OK] CompreFace already extracted
) else (
    echo Extracting files...
    powershell -Command "Expand-Archive -Path '%COMPREFACE_ZIP%' -DestinationPath 'CompreFace_%COMPREFACE_VERSION%' -Force"
    
    if not exist "CompreFace_%COMPREFACE_VERSION%" (
        echo [ERROR] Failed to extract CompreFace
        pause
        exit /b 1
    )
    echo [OK] Extraction complete
)

:: Start CompreFace
echo.
echo [6/6] Starting CompreFace...
cd CompreFace_%COMPREFACE_VERSION%

:: Check if port 8000 is already in use
netstat -ano | find ":8000" >nul
if %errorLevel% equ 0 (
    echo [WARNING] Port 8000 is already in use
    echo Please make sure no other application is using port 8000
    choice /c YN /m "Continue anyway?"
    if errorlevel 2 exit /b
)

echo Starting CompreFace with Docker Compose...
echo This may take a few minutes on first run...
docker-compose up -d

if %errorLevel% neq 0 (
    echo [ERROR] Failed to start CompreFace
    pause
    exit /b 1
)

cd ..

echo.
echo ========================================
echo    INSTALLATION COMPLETE!
echo ========================================
echo.
echo CompreFace is now running at: http://localhost:8000
echo.
echo Next steps:
echo 1. Open http://localhost:8000 in your browser
echo 2. Create an admin account
echo 3. Create a "recognition" service
echo 4. Copy the API key
echo 5. Add to your .env file:
echo    COMPREFACE_URL=http://localhost:8000
echo    COMPREFACE_API_KEY=your_api_key_here
echo.
echo To stop CompreFace: cd CompreFace_1.2.0 ^&^& docker-compose stop
echo To start CompreFace: cd CompreFace_1.2.0 ^&^& docker-compose start
echo To view logs: cd CompreFace_1.2.0 ^&^& docker-compose logs -f
echo.
echo Press any key to open CompreFace in your browser...
pause >nul
start http://localhost:8000