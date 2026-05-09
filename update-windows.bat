@echo off
setlocal enabledelayedexpansion

echo ============================================
echo   CondoPro - Actualizacion Rapida Windows
echo ============================================
echo.

:: CONFIGURACION
set "PROJECT_DIR=C:\xampp\php\www\CondoPro"
set "PHP_DIR=C:\xampp\php"

cd /d "%PROJECT_DIR%"

echo [1/4] Descargando cambios...
git pull origin main
if errorlevel 1 (
    echo ERROR: Error al descargar cambios.
    pause
    exit /b 1
)

echo [2/4] Actualizando dependencias...
"%PHP_DIR%\php.exe" "%PROJECT_DIR%\composer.phar" install --no-dev --optimize-autoloader 2>nul
if errorlevel 1 (
    composer install --no-dev --optimize-autoloader
)

echo [3/4] Ejecutando migraciones...
"%PHP_DIR%\php.exe" artisan migrate --force

echo [4/4] Optimizando...
"%PHP_DIR%\php.exe" artisan config:cache
"%PHP_DIR%\php.exe" artisan view:cache

echo.
echo Actualizacion completada!
pause