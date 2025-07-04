@echo off
REM EngChat - Laravel Sail Helper for Windows
REM Usage: sail.bat [command]

if "%1"=="" (
    echo EngChat Sail Commands:
    echo   up         - Start all containers
    echo   down       - Stop all containers  
    echo   build      - Build containers
    echo   artisan    - Run artisan commands
    echo   composer   - Run composer commands
    echo   npm        - Run npm commands
    echo   test       - Run PHPUnit tests
    echo   migrate    - Run migrations
    echo   seed       - Run seeders
    echo   fresh      - Fresh migration with seed
    echo   reverb     - Start Reverb WebSocket server
    echo   logs       - Show container logs
    echo   shell      - Access container shell
    goto :EOF
)

set SAIL_CMD=./vendor/bin/sail

if "%1"=="up" (
    %SAIL_CMD% up -d
    echo.
    echo ✅ EngChat containers started!
    echo 🌐 App: http://localhost:8000
    echo 📧 Mailpit: http://localhost:8025  
    echo 🐰 RabbitMQ: http://localhost:15672
    echo 📊 Swagger: http://localhost:8000/api/documentation
    goto :EOF
)

if "%1"=="down" (
    %SAIL_CMD% down
    echo ✅ Containers stopped!
    goto :EOF
)

if "%1"=="build" (
    %SAIL_CMD% build --no-cache
    goto :EOF
)

if "%1"=="migrate" (
    %SAIL_CMD% artisan migrate
    goto :EOF
)

if "%1"=="seed" (
    %SAIL_CMD% artisan db:seed
    goto :EOF
)

if "%1"=="fresh" (
    %SAIL_CMD% artisan migrate:fresh --seed
    goto :EOF
)

if "%1"=="reverb" (
    %SAIL_CMD% artisan reverb:start --debug
    goto :EOF
)

if "%1"=="logs" (
    %SAIL_CMD% logs -f
    goto :EOF
)

if "%1"=="shell" (
    %SAIL_CMD% shell
    goto :EOF
)

if "%1"=="test" (
    %SAIL_CMD% test
    goto :EOF
)

REM Pass through other commands
%SAIL_CMD% %*
