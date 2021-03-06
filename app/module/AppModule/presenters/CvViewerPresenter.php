<?php

namespace App\AppModule\Presenters;

use App\Model\Entity\Cv;
use App\Model\Facade\CvFacade;
use Exception;
use Latte\Engine;
use Nette\Http\Request;
use PdfResponse\PdfResponse;

class CvViewerPresenter extends BasePresenter
{

	/** @var CvFacade @inject */
	public $cvFacade;

	/** @var Request @inject */
	public $httpRequest;

	/** @var Cv */
	private $cv;

	private function setCv($id)
	{
		if ($this->cv) {
			return;
		}
		$candidate = $this->user->identity->candidate;

		if ($id) {
			$cvDao = $this->em->getDao(Cv::getClassName());
			$findedCv = $cvDao->find($id);
			$isOwnCv = $candidate && $findedCv->candidate->id === $candidate->id;
			$canViewForeignCv = $findedCv && $this->user->isAllowed('cvViewer', 'viewForeign');
			if ($isOwnCv || $canViewForeignCv || $findedCv->isPublic) {
				$this->cv = $findedCv;
			}
		} else if ($candidate) { // pro kandidáta načti defaultní
			$this->cv = $this->cvFacade->getDefaultCvOrCreate($candidate);
		}

		if (!$this->cv) {
			throw new Exception('Requested CV wasn\'t found.');
		}
	}

	/**
	 * @secured
	 * @resource('cvViewer')
	 * @privilege('default')
	 */
	public function actionDefault($id = NULL, $print = FALSE, $theme = NULL)
	{
		$this->setCv($id);
		
		switch ($theme) {
			case 'default':
			case 'europass':
			case 'standard1':
			case 'standard2':
				break;
			default:
				$theme = $this->cv->theme;
				break;
		}
		if ($theme === NULL) {
			$theme = 'default';
		}
		
		$templateParams = [
			'cv' => $this->cv,
			'candidate' => $this->cv->candidate,
			'person' => $this->cv->candidate->person,
			'user' => $this->cv->candidate->person->user,
			'theme' => $theme,
			'basePath' => $this->httpRequest->url->basePath,
			'lang' => $this->lang,
		];

		$latte = new Engine;
		$latte->addFilter('translate', $this->translator === NULL ? NULL : array($this->translator, 'translate'));
		$latte->addFilter('size', ['App\Model\Entity\Image', 'returnSizedFilename']);
		
		$templatePath = realpath(__DIR__ . '/../templates/' . $this->pureName . '/@pdf.layout.latte');
		$html = $latte->renderToString($templatePath, $templateParams);
		$pdf = new PdfResponse($html);
		$pdf->pageMargins = "23,15,26,15,9,9";
		$pdf->documentTitle = $this->cv->name;
		$pdf->documentAuthor = 'Source-Code.com';
		if ($print) {
			$pdf->mPDF->OpenPrintDialog();
		}
		
		$this->sendResponse($pdf);
	}

    public function getPureName()
	{
		$pos = strrpos($this->name, ':');
		if (is_int($pos)) {
			return substr($this->name, $pos + 1);
		}

		return $this->name;
	}
}
