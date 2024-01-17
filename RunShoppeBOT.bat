@echo off
SET PHP_PATH=
FOR /F "tokens=*" %%i IN ('where php') DO SET PHP_PATH=%%i

IF NOT EXIST "%PHP_PATH%" (
    echo PHP.exe not found.
    pause
    exit /b
)

    start cmd /k php RUNShopee.php
