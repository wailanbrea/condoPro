@echo off
setlocal enabledelayedexpansion

:: ============================================
::  CondoPro - Deployment Script for Windows
::  Laravel on XAMPP (Production)
:: ============================================

title CondoPro Deploy

:: ---- Paths ----
set "PROJECT_DIR=C:\xampp\htdocs\condoPro"
set "PHP_EXE=C:\xampp\php\php.exe"
set "MYSQL_EXE=C:\xampp\mysql\bin\mysql.exe"
set "APACHE_EXE=C:\xampp\apache\bin\httpd.exe"
set "COMPOSER_PHAR=C:\xampp\php\composer.phar"

:: ---- Database ----
set "DB_NAME=condopro"
set "DB_USER=root"
set "DB_PASS="
set "DB_HOST=127.0.0.1"
set "DB_PORT=3306"

:: ---- App ----
set "APP_NAME=CondoPro"
set "APP_ENV=production"
set "APP_DEBUG=false"
set "APP_URL=https://condopro.bsolutions.dev"

:: ---- Logs ----
set "APACHE_LOG=C:\xampp\apache\logs\condopro-error.log"
set "LARAVEL_LOG=%PROJECT_DIR%\storage\logs\laravel.log"

:: ============================================
::  STEP 0: Banner
:: ============================================
echo.
echo  ============================================
echo   CondoPro - Deploy Script
echo   %APP_URL%
echo  ============================================
echo.

:: ============================================
::  STEP 1: Validate project directory exists
:: ============================================
echo  [1/18] Validando directorio del proyecto...

if not exist "%PROJECT_DIR%" (
    echo.
    echo  ERROR: No se encontro el directorio del proyecto:
    echo         %PROJECT_DIR%
    echo.
    echo  Asegurese de que el proyecto este clonado en esa ruta.
    pause
    exit /b 1
)
echo         Directorio encontrado: %PROJECT_DIR%
echo.

:: ============================================
::  STEP 2: Validate it is a Laravel project
:: ============================================
echo  [2/18] Validando proyecto Laravel...

if not exist "%PROJECT_DIR%\artisan" (
    echo.
    echo  ERROR: No se encontro artisan en:
    echo         %PROJECT_DIR%\artisan
    echo.
    echo  Esto no parece ser un proyecto Laravel valido.
    pause
    exit /b 1
)
echo         artisan: OK

if not exist "%PROJECT_DIR%\public\index.php" (
    echo.
    echo  ERROR: No se encontro public\index.php en:
    echo         %PROJECT_DIR%\public\index.php
    echo.
    echo  Esto no parece ser un proyecto Laravel valido.
    pause
    exit /b 1
)
echo         public\index.php: OK
echo.

:: ============================================
::  STEP 3: Validate PHP
:: ============================================
echo  [3/18] Validando PHP...

if not exist "%PHP_EXE%" (
    echo.
    echo  ERROR: PHP no encontrado en:
    echo         %PHP_EXE%
    echo.
    echo  Instale XAMPP con PHP 8.2 o superior.
    pause
    exit /b 1
)
for /f "tokens=*" %%v in ('"%PHP_EXE%" -r "echo PHP_VERSION;"') do set "PHP_VER=%%v"
echo         PHP %PHP_VER% encontrado: %PHP_EXE%
echo.

:: ============================================
::  STEP 4: Validate MySQL
:: ============================================
echo  [4/18] Validando MySQL...

if not exist "%MYSQL_EXE%" (
    echo.
    echo  ERROR: MySQL no encontrado en:
    echo         %MYSQL_EXE%
    echo.
    echo  Instale XAMPP con MySQL/MariaDB.
    pause
    exit /b 1
)
for /f "tokens=*" %%v in ('"%MYSQL_EXE%" -u %DB_USER% -e "SELECT VERSION();" 2^>nul') do set "MYSQL_VER=%%v"
echo         MySQL encontrado: %MYSQL_EXE%
echo.

:: ============================================
::  STEP 5: Validate Apache
:: ============================================
echo  [5/18] Validando Apache...

if not exist "%APACHE_EXE%" (
    echo.
    echo  ERROR: Apache no encontrado en:
    echo         %APACHE_EXE%
    pause
    exit /b 1
)
echo         Apache encontrado: %APACHE_EXE%
echo.

:: ============================================
::  STEP 6: Validate Composer
:: ============================================
echo  [6/18] Validando Composer...

where composer >nul 2>&1
if not errorlevel 1 (
    set "COMPOSER_CMD=composer"
    echo         Composer global detectado
) else if exist "%COMPOSER_PHAR%" (
    set "COMPOSER_CMD=%PHP_EXE% %COMPOSER_PHAR%"
    echo         Composer encontrado: %COMPOSER_PHAR%
) else (
    echo.
    echo  ERROR: Composer no encontrado.
    echo         No se encontro como comando global ni en:
    echo         %COMPOSER_PHAR%
    pause
    exit /b 1
)
echo.

:: ============================================
::  STEP 7: Enter project directory
:: ============================================
echo  [7/18] Entrando al directorio del proyecto...

cd /d "%PROJECT_DIR%"
echo         Directorio actual: %CD%
echo.

:: ============================================
::  STEP 8: Install Composer dependencies
:: ============================================
echo  [8/18] Instalando dependencias...

%COMPOSER_CMD% install --no-dev --optimize-autoloader
if errorlevel 1 (
    echo.
    echo  ERROR: Fallo la instalacion de dependencias con Composer.
    pause
    exit /b 1
)
echo         Dependencias instaladas correctamente.
echo.

:: ============================================
::  STEP 9: Handle .env file
:: ============================================
echo  [9/18] Configurando archivo .env...

if exist "%PROJECT_DIR%\.env" (
    echo         .env ya existe. Creando backup...

    set "TIMESTAMP="
    for /f "tokens=*" %%t in ('powershell -NoProfile -Command "Get-Date -Format 'yyyyMMdd_HHmmss'"') do set "TIMESTAMP=%%t"

    copy "%PROJECT_DIR%\.env" "%PROJECT_DIR%\.env.backup_!TIMESTAMP!" >nul 2>&1
    if errorlevel 1 (
        copy "%PROJECT_DIR%\.env" "%PROJECT_DIR%\.env.backup" >nul 2>&1
        echo         Backup creado: .env.backup
    ) else (
        echo         Backup creado: .env.backup_!TIMESTAMP!
    )
) else (
    if exist "%PROJECT_DIR%\.env.example" (
        echo         Copiando .env.example a .env...
        copy "%PROJECT_DIR%\.env.example" "%PROJECT_DIR%\.env" >nul
        echo         .env creado desde .env.example
    ) else (
        echo         Creando .env nuevo...
        (
            echo APP_NAME=%APP_NAME%
            echo APP_ENV=%APP_ENV%
            echo APP_KEY=
            echo APP_DEBUG=%APP_DEBUG%
            echo APP_URL=%APP_URL%
            echo.
            echo LOG_CHANNEL=stack
            echo.
            echo DB_CONNECTION=mysql
            echo DB_HOST=%DB_HOST%
            echo DB_PORT=%DB_PORT%
            echo DB_DATABASE=%DB_NAME%
            echo DB_USERNAME=%DB_USER%
            echo DB_PASSWORD=
            echo.
            echo MAIL_MAILER=smtp
            echo MAIL_HOST=mailpit
            echo MAIL_PORT=1025
            echo MAIL_USERNAME=null
            echo MAIL_PASSWORD=null
            echo MAIL_ENCRYPTION=null
            echo MAIL_FROM_ADDRESS="hello@example.com"
            echo MAIL_FROM_NAME="CondoPro"
        ) > "%PROJECT_DIR%\.env"
        echo         .env creado con valores por defecto
    )
)
echo.

:: ============================================
::  STEP 10: Update .env values safely
:: ============================================
echo  [10/18] Actualizando valores en .env...

powershell -NoProfile -Command ^
    "$envFile = '%PROJECT_DIR%\.env';" ^
    "$content = Get-Content $envFile;" ^
    "$pairs = @{" ^
    "  'APP_NAME'='\"CondoPro\"';" ^
    "  'APP_ENV'='production';" ^
    "  'APP_DEBUG'='false';" ^
    "  'APP_URL'='https://condopro.bsolutions.dev';" ^
    "  'DB_CONNECTION'='mysql';" ^
    "  'DB_HOST'='127.0.0.1';" ^
    "  'DB_PORT'='3306';" ^
    "  'DB_DATABASE'='condopro';" ^
    "  'DB_USERNAME'='root';" ^
    "  'DB_PASSWORD'=''" ^
    "};" ^
    "foreach ($key in $pairs.Keys) {" ^
    "  $value = $pairs[$key];" ^
    "  $found = $false;" ^
    "  for ($i = 0; $i -lt $content.Length; $i++) {" ^
    "    if ($content[$i] -match '^\s*' + [regex]::Escape($key) + '\s*=') {" ^
    "      $content[$i] = $key + '=' + $value;" ^
    "      $found = $true;" ^
    "      break" ^
    "    }" ^
    "  }" ^
    "  if (-not $found) {" ^
    "    $content += $key + '=' + $value" ^
    "  }" ^
    "}" ^
    "Set-Content -Path $envFile -Value $content -Encoding UTF8"

if errorlevel 1 (
    echo  ERROR: Fallo la actualizacion del archivo .env.
    pause
    exit /b 1
)
echo         Valores actualizados en .env correctamente.
echo.

:: ============================================
::  STEP 11: Create database if not exists
:: ============================================
echo  [11/18] Creando base de datos si no existe...

if "%DB_PASS%"=="" (
    "%MYSQL_EXE%" -u %DB_USER% -h %DB_HOST% -P %DB_PORT% -e "CREATE DATABASE IF NOT EXISTS `%DB_NAME%` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
) else (
    "%MYSQL_EXE%" -u %DB_USER% -p%DB_PASS% -h %DB_HOST% -P %DB_PORT% -e "CREATE DATABASE IF NOT EXISTS `%DB_NAME%` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
)

if errorlevel 1 (
    echo.
    echo  ERROR: No se pudo crear la base de datos.
    echo         Verifique que MySQL este ejecutandose.
    pause
    exit /b 1
)
echo         Base de datos '%DB_NAME%' verificada/creada.
echo.

:: ============================================
::  STEP 12: Generate application key
:: ============================================
echo  [12/18] Generando APP_KEY...

"%PHP_EXE%" artisan key:generate --force
if errorlevel 1 (
    echo.
    echo  ERROR: Fallo la generacion de APP_KEY.
    pause
    exit /b 1
)
echo         APP_KEY generada correctamente.
echo.

:: ============================================
::  STEP 13: Run migrations
:: ============================================
echo  [13/18] Ejecutando migraciones...

"%PHP_EXE%" artisan migrate --force
if errorlevel 1 (
    echo.
    echo  ERROR: Fallo la ejecucion de migraciones.
    echo         Verifique la conexion a la base de datos en .env
    pause
    exit /b 1
)
echo         Migraciones ejecutadas correctamente.
echo.

:: ============================================
::  STEP 14: Optional seeders
:: ============================================
echo  [14/18] Ejecutar seeders? (Datos de prueba iniciales)
echo.
echo         ATENCION: Esto eliminara todos los datos existentes
echo         y cargara datos iniciales de prueba.
echo.
set /p "RUN_SEED=Desea ejecutar db:seed? (S/N): "

if /i "!RUN_SEED!"=="S" (
    echo         Ejecutando seeders...
    "%PHP_EXE%" artisan db:seed --force
    if errorlevel 1 (
        echo.
        echo  ERROR: Fallo la ejecucion de seeders.
        echo         Verifique que las tablas existan.
    ) else (
        echo         Seeders ejecutados correctamente.
    )
) else (
    echo         Seeders omitidos por el usuario.
)
echo.

:: ============================================
::  STEP 15: Storage link + directories
:: ============================================
echo  [15/18] Configurando storage y directorios...

"%PHP_EXE%" artisan storage:link 2>nul
echo         storage:link OK

if not exist "%PROJECT_DIR%\storage\logs" (
    mkdir "%PROJECT_DIR%\storage\logs"
    echo         storage\logs creado
) else (
    echo         storage\logs ya existe
)

if not exist "%PROJECT_DIR%\bootstrap\cache" (
    mkdir "%PROJECT_DIR%\bootstrap\cache"
    echo         bootstrap\cache creado
) else (
    echo         bootstrap\cache ya existe
)
echo.

:: ============================================
::  STEP 16: Clear cache
:: ============================================
echo  [16/18] Limpiando cache...

"%PHP_EXE%" artisan optimize:clear
echo         Cache limpiada.
echo.

:: ============================================
::  STEP 17: Optimize (config + route + view cache)
:: ============================================
echo  [17/18] Optimizando aplicacion...

"%PHP_EXE%" artisan config:cache
if errorlevel 1 (
    echo         ADVERTENCIA: Fallo config:cache
) else (
    echo         config:cache OK
)

"%PHP_EXE%" artisan route:cache
if errorlevel 1 (
    echo         ADVERTENCIA: Fallo route:cache
) else (
    echo         route:cache OK
)

"%PHP_EXE%" artisan view:cache
if errorlevel 1 (
    echo         ADVERTENCIA: Fallo view:cache
) else (
    echo         view:cache OK
)
echo.

:: ============================================
::  STEP 18: Validate Apache syntax + restart
:: ============================================
echo  [18/18] Validando Apache y reiniciando...

"%APACHE_EXE%" -t 2>&1
if errorlevel 1 (
    echo.
    echo  ADVERTENCIA: La configuracion de Apache tiene errores.
    echo         No se reiniciara Apache.
    echo         Revise la configuracion antes de continuar.
    echo.
) else (
    echo         Sintaxis Apache OK. Reiniciando...
    "%APACHE_EXE%" -k restart 2>&1
    if errorlevel 1 (
        echo         ADVERTENCIA: No se pudo reiniciar Apache automaticamente.
        echo         Reinicielo manualmente desde el panel de XAMPP.
    ) else (
        echo         Apache reiniciado correctamente.
    )
)
echo.

:: ============================================
::  DEPLOYMENT SUMMARY
:: ============================================
echo  ============================================
echo   DEPLOY COMPLETADO
echo  ============================================
echo.
echo   URL del sistema:     %APP_URL%
echo   Directorio:          %PROJECT_DIR%
echo   Base de datos:       %DB_NAME% en %DB_HOST%:%DB_PORT%
echo   Usuario BD:          %DB_USER%
echo   Entorno:             %APP_ENV%
echo   Debug:               %APP_DEBUG%
echo.
echo   Credenciales de prueba (si ejecuto seeders):
echo     Super Admin:  admin@condopro.com / password
echo     Admin:        carlos@natalie13.com / password
echo.
echo   Logs para revisar si falla:
echo     Apache:  %APACHE_LOG%
echo     Laravel:  %LARAVEL_LOG%
echo.
echo   Comandos utiles:
echo     Limpiar cache:      %PHP_EXE% artisan optimize:clear
echo     Ver rutas:          %PHP_EXE% artisan route:list
echo     Migrar:             %PHP_EXE% artisan migrate --force
echo     Reiniciar Apache:   %APACHE_EXE% -k restart
echo.
echo  ============================================
echo.
pause