@echo off
title Hospital Management System - Local Server
echo ============================================
echo   Hospital Management System
echo   Local Development Server
echo ============================================
echo.

REM Try to find PHP in common locations
set PHP_PATH=

if exist "C:\php\php.exe" set PHP_PATH=C:\php\php.exe
if exist "C:\xampp\php\php.exe" set PHP_PATH=C:\xampp\php\php.exe
if exist "C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe" set PHP_PATH=C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe
if exist "C:\laragon\bin\php\php-8.2.0-Win32-vs16-x64\php.exe" set PHP_PATH=C:\laragon\bin\php\php-8.2.0-Win32-vs16-x64\php.exe

REM Check if PHP is in PATH
where php >nul 2>nul
if %ERRORLEVEL% EQU 0 set PHP_PATH=php

if "%PHP_PATH%"=="" (
    echo ERROR: PHP not found!
    echo.
    echo Please install PHP from one of these options:
    echo   1. PHP: https://windows.php.net/download/
    echo   2. XAMPP: https://www.apachefriends.org/
    echo   3. Laragon: https://laragon.org/download/
    echo.
    pause
    exit /b 1
)

echo PHP found: %PHP_PATH%
echo.
echo Starting server at http://localhost:8000
echo.
echo Demo Login Credentials:
echo   Admin: admin / admin123
echo   Doctor: doctor / doctor123
echo   Receptionist: receptionist / reception123
echo.
echo Press Ctrl+C to stop the server
echo ============================================
echo.

start http://localhost:8000
"%PHP_PATH%" -S localhost:8000

pause
