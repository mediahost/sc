<?php

namespace App\FrontModule\Presenters;

use Nette;
use Tracy\Debugger as Debug;

/**
 * Deny presenter.
 */
class InstallPresenter extends BasePresenter
{

    /** @var string */
    private $tempDir;

    /** @var string */
    private $wwwDir;

    /** @var string */
    private $installDir;

    public function setPathes($tempDir, $wwwDir)
    {
        $this->tempDir = $tempDir;
        $this->wwwDir = $wwwDir;
        $this->installDir = $this->tempDir . "/install";
    }

    protected function startup()
    {
        parent::startup();
        if (!is_dir($this->installDir)) {
            mkdir($this->installDir);
        }
    }

    public function actionDefault()
    {
        $this->installAdminer();
        $this->terminate();
    }

    private function installAdminer()
    {
        $lockFile = $this->installDir . "/adminer";
        if (!file_exists($lockFile)) {
            chmod($this->wwwDir . "/adminer/database.sql", 0777);
            $this->lockFile($lockFile);
        }
    }

    private function lockFile($lockFile)
    {
        file_put_contents($lockFile, "1");
    }

}
