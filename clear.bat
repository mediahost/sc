set root=%CD%
cd /d "temp"
del "btfj.dat"
rd /s /q cache
rd /s /q proxies
cd /d "%root%"