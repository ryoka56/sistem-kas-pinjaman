@echo off
cd /d "%~dp0"
start "MySQL" "D:\Project PBO web\tools\mysql\bin\mysqld.exe" --defaults-file="D:\Project PBO web\tools\mysql\my.ini" --console
timeout /t 6 >nul
php artisan serve --host=127.0.0.1 --port=8000
