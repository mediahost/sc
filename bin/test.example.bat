set this=%CD%
set root=%this%/..
set phpIni="c:/xampp/php/php.ini"
set testDir="%root%/tests"
set testLog=%root%/tests/test.log
cd /d "%root%/vendor/bin"
start tester.bat -c %phpIni% -log %testLog% --stop-on-fail -w %testDir% %testDir%
cd /d "%this%"
