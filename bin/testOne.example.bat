set this=%CD%
set root=%this%/..
cd /d "%root%/temp"
del "btfj.dat"
rd /s /q cache
rd /s /q proxies
rd /s /q install
cd /d "%this%"

cd /d "%root%/tests/temp"
del "btfj.dat"
rd /s /q cache
cd /d "%root%/tests"
del "test.log"
cd /d "%this%"

set phpIni="c:/xampp/php/php.ini"
set testDir="%root%/tests/src/"
set testFile="%root%/tests/src/extensions/Installer/Installer.phpt"
set testLog=%root%/tests/test.log
cd /d "%root%/vendor/bin"
start tester.bat -c %phpIni% -log %testLog% --stop-on-fail -w %testDir% %testFile%
cd /d "%this%"
