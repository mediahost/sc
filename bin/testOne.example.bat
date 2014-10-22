set this=%CD%
set root=%this%/..
set phpIni="c:/xampp/php/php.ini"
set testDir="%root%/tests/"
set testFile="%root%/tests/model/installer/Installer.phpt"
set testLog=%root%/tests/test.log
cd /d "%root%/vendor/bin"
start tester.bat -c %phpIni% -log %testLog% --stop-on-fail -w %testDir% %testFile%
cd /d "%this%"
