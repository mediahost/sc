@ECHO OFF
SET BIN_TARGET=%~dp0../vendor/dg/ftp-deployment/Deployment/deployment
php "%BIN_TARGET%" %~dp0/deployment.php%*
