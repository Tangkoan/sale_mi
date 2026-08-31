@echo off
title Laravel Queue Worker
echo Starting Laravel Queue Worker...

cd /d C:\wamp64\www\sale_mi\sale_mi
C:\wamp64\bin\php\php8.4.15\php.exe artisan queue:work --tries=5

pause