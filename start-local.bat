@echo off
setlocal
set PROJECT_DIR=%~dp0
echo Lam Shaml local helper
echo.
if not exist "C:\xampp\xampp-control.exe" (
  echo XAMPP was not found at C:\xampp.
  echo Install XAMPP or update LOCAL_SETUP.md with your custom path.
  pause
  exit /b 1
)
echo Project folder: %PROJECT_DIR%
echo Expected htdocs path: C:\xampp\htdocs\lam-shaml
echo.
echo Open XAMPP Control Panel and start Apache + MySQL:
echo C:\xampp\xampp-control.exe
echo.
echo Site:
echo http://localhost/lam-shaml/public/
echo.
echo Health:
echo http://localhost/lam-shaml/public/health
echo.
echo phpMyAdmin:
echo http://localhost/phpmyadmin/
echo.
echo To install database from this folder:
echo C:\xampp\php\php.exe database\install.php
echo.
start "" "C:\xampp\xampp-control.exe"
pause
