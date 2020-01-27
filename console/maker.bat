@echo off

set directpath=%~dp0

set arg1=%1
set arg2=%2

SHIFT
SHIFT

:loop
IF NOT "%1"=="" (
    IF "%1"=="-m" (
        SET model=%2
        SHIFT
    )    

    SHIFT
    GOTO :loop
)

REM php start.php --task=make:api --class=UsersAPI --model=Session
php %directpath%start.php --task=%arg1% --class=%arg2% --model=%model%
exit