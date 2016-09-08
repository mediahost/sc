@ECHO OFF
SET BIN_TARGET=%~dp0../vendor/dg/ftp-deployment/src/deployment.php
php "%BIN_TARGET%" %~dp0/deployment_ver21.php%*
