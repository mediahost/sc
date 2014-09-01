set this=%CD%
set root=%this%/..
set phpIni="c:\Program Files (x86)\EasyPHP-DevServer-13.1VC9\binaries\php\php_runningversion"
set testDir="%root%/tests"
set testLog=%root%/tests/test.log
cd /d "%root%/vendor/bin"
start tester.bat -c %phpIni% -log %testLog% -w %testDir% %testDir%
cd /d "%this%"