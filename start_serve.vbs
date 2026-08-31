Set WshShell = CreateObject("WScript.Shell")
' បើក Laravel Server (Port 8000) លាក់ផ្ទាំង (0)
WshShell.Run "cmd.exe /c cd /d C:\wamp64\www\sale_mi\sale_mi && C:\wamp64\bin\php\php8.4.15\php.exe artisan serve --host=0.0.0.0 --port=8000", 0, False