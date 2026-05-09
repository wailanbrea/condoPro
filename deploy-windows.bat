@echo off
setlocal enabledelayedexpansion

echo ============================================
echo   CondoPro - Script de Despliegue Windows
echo ============================================
echo.

:: ============================================
:: CONFIGURACION - Cambiar estos valores
:: ============================================
set "REPO_URL=https://github.com/wailanbrea/condoPro.git"
set "PROJECT_DIR=C:\xampp\php\www\CondoPro"
set "PHP_DIR=C:\xampp\php"
set "APACHE_DIR=C:\xampp\apache"
set "MYSQL_DIR=C:\xampp\mysql"
set "DB_NAME=condopro"
set "DB_USER=root"
set "DB_PASS="
set "APP_URL=http://localhost"
set "APP_ENV=production"

:: ============================================
:: PASO 1: Verificar prerequisitos
:: ============================================
echo [1/8] Verificando prerequisitos...

if not exist "%PHP_DIR%\php.exe" (
    echo ERROR: PHP no encontrado en %PHP_DIR%
    echo Instala XAMPP desde https://www.apachefriends.org/
    pause
    exit /b 1
)

if not exist "%MYSQL_DIR%\bin\mysql.exe" (
    echo ERROR: MySQL no encontrado en %MYSQL_DIR%
    pause
    exit /b 1
)

where git >nul 2>&1
if errorlevel 1 (
    echo ERROR: Git no esta instalado.
    echo Descarga Git desde https://git-scm.com/download/win
    pause
    exit /b 1
)

echo      PHP: OK
echo      MySQL: OK
echo      Git: OK
echo.

:: ============================================
:: PASO 2: Clonar o actualizar el repositorio
:: ============================================
echo [2/8] Descargando codigo del repositorio...

if exist "%PROJECT_DIR%\.git" (
    echo      Actualizando repositorio existente...
    cd /d "%PROJECT_DIR%"
    git fetch origin
    git reset --hard origin/main
) else (
    echo      Clonando repositorio...
    git clone %REPO_URL% "%PROJECT_DIR%"
    cd /d "%PROJECT_DIR%"
)

if errorlevel 1 (
    echo ERROR: Error al clonar/actualizar el repositorio.
    pause
    exit /b 1
)
echo.

:: ============================================
:: PASO 3: Instalar dependencias Composer
:: ============================================
echo [3/8] Instalando dependencias...

where composer >nul 2>&1
if errorlevel 1 (
    if exist "%PHP_DIR%\composer.phar" (
        "%PHP_DIR%\php.exe" "%PHP_DIR%\composer.phar" install --no-dev --optimize-autoloader
    ) else (
        echo      Descargando Composer...
        "%PHP_DIR%\php.exe" -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        "%PHP_DIR%\php.exe" composer-setup.php --install-dir="%PHP_DIR%" --filename=composer.phar
        del composer-setup.php
        "%PHP_DIR%\php.exe" "%PHP_DIR%\composer.phar" install --no-dev --optimize-autoloader
    )
) else (
    composer install --no-dev --optimize-autoloader
)

if errorlevel 1 (
    echo ERROR: Error al instalar dependencias.
    pause
    exit /b 1
)
echo.

:: ============================================
:: PASO 4: Configurar .env
:: ============================================
echo [4/8] Configurando entorno...

cd /d "%PROJECT_DIR%"

if not exist ".env" (
    if exist ".env.example" (
        copy .env.example .env >nul
        echo      .env creado desde .env.example
    ) else (
        (
            echo APP_NAME=CondoPro
            echo APP_ENV=%APP_ENV%
            echo APP_KEY=
            echo APP_DEBUG=false
            echo APP_URL=%APP_URL%
            echo.
            echo LOG_CHANNEL=stack
            echo.
            echo DB_CONNECTION=mysql
            echo DB_HOST=127.0.0.1
            echo DB_PORT=3306
            echo DB_DATABASE=%DB_NAME%
            echo DB_USERNAME=%DB_USER%
            echo DB_PASSWORD=%DB_PASS%
            echo.
            echo MAIL_MAILER=smtp
            echo MAIL_HOST=mailpit
            echo MAIL_PORT=1025
            echo MAIL_USERNAME=null
            echo MAIL_PASSWORD=null
            echo MAIL_ENCRYPTION=null
            echo MAIL_FROM_ADDRESS="hello@example.com"
            echo MAIL_FROM_NAME="CondoPro"
        ) > .env
        echo      .env creado con valores por defecto
    )
) else (
    echo      .env ya existe, manteniendo configuracion actual
)
echo.

:: ============================================
:: PASO 5: Generar clave de aplicacion
:: ============================================
echo [5/8] Generando APP_KEY...

"%PHP_DIR%\php.exe" artisan key:generate --force
echo.

:: ============================================
:: PASO 6: Crear base de datos y ejecutar migraciones
:: ============================================
echo [6/8] Configurando base de datos...

echo      Creando base de datos %DB_NAME%...
"%MYSQL_DIR%\bin\mysql.exe" -u %DB_USER% -p%DB_PASS% -e "CREATE DATABASE IF NOT EXISTS `%DB_NAME%` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
if errorlevel 1 (
    echo      Intentando sin contrasena...
    "%MYSQL_DIR%\bin\mysql.exe" -u %DB_USER% -e "CREATE DATABASE IF NOT EXISTS `%DB_NAME%` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
)

echo      Ejecutando migraciones...
"%PHP_DIR%\php.exe" artisan migrate --force

if errorlevel 1 (
    echo ERROR: Error en migraciones. Verifica la conexion a la base de datos.
    pause
    exit /b 1
)
echo.

:: ============================================
:: PASO 7: Optimizar y generar cache
:: ============================================
echo [7/8] Optimizando aplicacion...

"%PHP_DIR%\php.exe" artisan config:cache
"%PHP_DIR%\php.exe" artisan route:cache 2>nul
"%PHP_DIR%\php.exe" artisan view:cache
"%PHP_DIR%\php.exe" artisan storage:link 2>nul
echo.

:: ============================================
:: PASO 8: Configurar tarea programada (Task Scheduler)
:: ============================================
echo [8/8] Configurando tarea programada...

set "TASK_NAME=CondoPro_Scheduler"
set "PHP_PATH=%PHP_DIR%\php.exe"
set "ARTISAN_PATH=%PROJECT_DIR%\artisan"

schtasks /query /tn "%TASK_NAME%" >nul 2>&1
if errorlevel 1 (
    echo      Creando tarea programada para scheduler...
    schtasks /create /tn "%TASK_NAME%" /tr "\"%PHP_PATH%\" \"%ARTISAN_PATH%\" schedule:run" /sc minute /mo 1 /ru SYSTEM
    if errorlevel 1 (
        echo      ADVERTENCIA: No se pudo crear la tarea. Ejecuta manualmente:
        echo      schtasks /create /tn "CondoPro_Scheduler" /tr "\"%PHP_PATH%\" \"%ARTISAN_PATH%\" schedule:run" /sc minute /mo 1
    )
) else (
    echo      Tarea programada ya existe. Omitiendo...
)

echo.
echo      Tambien necesitas esta tarea para marcar facturas vencidas:
schtasks /query /tn "CondoPro_OverdueBills" >nul 2>&1
if errorlevel 1 (
    schtasks /create /tn "CondoPro_OverdueBills" /tr "\"%PHP_PATH%\" \"%ARTISAN_PATH%\" bills:mark-overdue" /sc daily /st 00:30 /ru SYSTEM 2>nul
)
echo.

:: ============================================
:: RESUMEN
:: ============================================
echo ============================================
echo   Despliegue completado exitosamente!
echo ============================================
echo.
echo   Aplicacion: %APP_URL%
echo   Directorio: %PROJECT_DIR%
echo.
echo   PROXIMOS PASOS:
echo   1. Edita el archivo .env con tus datos reales:
echo      - DB_DATABASE, DB_USERNAME, DB_PASSWORD
echo      - MAIL_* para envio de correos
echo      - APP_URL con tu dominio
echo.
echo   2. Para cargar datos de prueba ejecuta:
echo      cd /d "%PROJECT_DIR%"
echo      "%PHP_DIR%\php.exe" artisan db:wipe --force
echo      "%PHP_DIR%\php.exe" artisan migrate --seed
echo.
echo   3. Reinicia Apache desde el panel de XAMPP
echo.
echo   4. Accede con estas credenciales:
echo      Super Admin: admin@condopro.com / password
echo      Admin: carlos@natalie13.com / password
echo.
echo ============================================
pause