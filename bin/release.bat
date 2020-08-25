:: Copyright 2020. Plesk International GmbH.

@echo off

FOR %%I IN ("%~dp0.") DO FOR %%J IN ("%%~dpI.") DO set PROJECT_DIR=%%~dpnxJ

set SRC_DIR=%TEMP%\solusiovps-%RANDOM%
set BUILD_DIR=%SRC_DIR%\modules\servers\solusiovps
set RELEASE_DIR=%PROJECT_DIR%\releases

mkdir "%BUILD_DIR%"

robocopy "%PROJECT_DIR%\lang" "%BUILD_DIR%\lang" /E
robocopy "%PROJECT_DIR%\lib" "%BUILD_DIR%\lib" /E
robocopy "%PROJECT_DIR%\pages" "%BUILD_DIR%\pages" /E
robocopy "%PROJECT_DIR%\templates" "%BUILD_DIR%\templates" /E

robocopy "%PROJECT_DIR%" "%BUILD_DIR%" composer.json
robocopy "%PROJECT_DIR%" "%BUILD_DIR%" composer.lock
robocopy "%PROJECT_DIR%" "%BUILD_DIR%" package.json
robocopy "%PROJECT_DIR%" "%BUILD_DIR%" package-lock.json
robocopy "%PROJECT_DIR%" "%BUILD_DIR%" solusiovps.php

cd "%BUILD_DIR%"

call composer install --no-dev
call npm ci

robocopy "%PROJECT_DIR%\bin" "%BUILD_DIR%\lang" index.php
robocopy "%PROJECT_DIR%\bin" "%BUILD_DIR%\lib" index.php
robocopy "%PROJECT_DIR%\bin" "%BUILD_DIR%\node_modules" index.php
robocopy "%PROJECT_DIR%\bin" "%BUILD_DIR%\pages" index.php
robocopy "%PROJECT_DIR%\bin" "%BUILD_DIR%\templates" index.php
robocopy "%PROJECT_DIR%\bin" "%BUILD_DIR%\vendor" index.php

del "%BUILD_DIR%\composer.json"
del "%BUILD_DIR%\composer.lock"
del "%BUILD_DIR%\package.json"
del "%BUILD_DIR%\package-lock.json"

if not exist "%RELEASE_DIR%" mkdir "%RELEASE_DIR%"

cd "%SRC_DIR%"

call zip -r "%RELEASE_DIR%\solusiovps-%RANDOM%.zip" *

cd "%TEMP%"

rmdir /s /q "%SRC_DIR%"
