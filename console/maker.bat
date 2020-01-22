@echo off

set directpath=%~dp0

set arg1=%1
set arg2=%2

php %directpath%start.php %arg1% %arg2%

exit