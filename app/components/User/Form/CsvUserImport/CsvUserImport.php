<?php

namespace App\Components\User\Form;

use App\Components\BaseControl;
use App\Extensions\Csv\Exceptions\BeforeProcessException;
use App\Extensions\Csv\Exceptions\WhileProcessException;
use App\Extensions\Csv\IParserFactory;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\ImportedUser;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use Nette\Utils\ArrayHash;

class CsvUserImport extends BaseControl
{

	/** @var IParserFactory @inject */
	public $iParserFactory;

	// <editor-fold desc="events">

	/** @var array */
	public $onSuccess = [];

	/** @var array */
	public $onFail = [];

	/** @var array */
	public $onDone = [];

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addUpload('file', 'CSV file');

		$form->addSubmit('save', 'Update users');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->processFile($values->file);
		$this->onDone();
	}

	private function getColumnsAliases()
	{
		$aliases = [
			0 => 'firstname',
			1 => 'surname',
			2 => 'mail',
			3 => 'country',
			4 => 'coreSkill',
			5 => 'otherSkill',
			6 => 'currentJob',
			7 => 'linkedinLink',
			8 => 'notes',
		];
		return $aliases;
	}

	public function parseRow(array $rowArray)
	{
		$row = ArrayHash::from($rowArray);
		$userRepo = $this->em->getRepository(ImportedUser::getClassName());
		/* @var $user ImportedUser */
		if (isset($row->mail) && $row->mail) {
			$user = $userRepo->findOneByMail($row->mail);
			if (!$user) {
				$user = new ImportedUser($row->mail);
			}
			$user->firstname = $row->firstname;
			$user->surname = $row->surname;
			$user->country = $row->country;
			$user->coreSkill = $row->coreSkill;
			$user->otherSkill = $row->otherSkill;
			$user->currentJob = $row->currentJob;
			$user->linkedinLink = $row->linkedinLink;
			$user->notes = $row->notes;

			$userRepo->save($user);
			return $user->id;
		}
		return FALSE;
	}

	private function processFile(FileUpload $file)
	{
		$csvParser = $this->iParserFactory->create();
		try {
			$csvParser
				->setCsv(';')
				->setFile($file)
				->setCallback($this->parseRow)
				->setRowChecker($this->getColumnsAliases(), FALSE);
			$executed = $csvParser->execute();
			$this->onSuccess($executed);
		} catch (BeforeProcessException $e) {
			$this->onFail($e->getMessage());
		} catch (WhileProcessException $e) {
			$this->onFail($e->getMessage());
			$this->onSuccess($e->getExecuted());
		}
		return $this;
	}

	/** @return array */
	protected function getDefaults()
	{
		$values = [];
		return $values;
	}

}

interface ICsvUserImportFactory
{

	/** @return CsvUserImport */
	function create();
}
