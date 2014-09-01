set this=%CD%
set root=%this%/..
cd /d "%root%/temp"
del "btfj.dat"
rd /s /q cache
rd /s /q proxies
rd /s /q install
cd /d "%this%"