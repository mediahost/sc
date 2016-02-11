<?php

namespace App\Components\Cv;

/**
 * Description of CvDocument
 */
class CvDocument extends \App\Components\BaseControl {
	
	/** @var Cv */
	private $cv;
	
	
	/**
	 * Seter for Cv entity
	 * @param Cv $cv
	 * @return \App\Components\Cv\CvDocument
	 */
	public function setCv(\App\Model\Entity\Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}
	
	public function generatePdf($fileName) 
	{
		$theme = $this->cv->theme;
		if ($theme === NULL) {
			$theme = 'default';
		}

		$templateParams = [
			'cv' => $this->cv,
			'candidate' => $this->cv->candidate,
			'theme' => $theme,
			'basePath' => $this->presenter->template->basePath,
			'lang' => $this->presenter->lang,
		];

		$latte = new \Nette\Latte\Engine();
		$latte->addFilter('translate', $this->translator === NULL ? NULL : array($this->translator, 'translate'));
		$latte->addFilter('size', ['App\Model\Entity\Image', 'returnSizedFilename']);

		$templatePath = realpath(__DIR__ . '/@pdf.layout.latte');
		$html = $latte->renderToString($templatePath, $templateParams);
		$pdf = new \PdfResponse\PdfResponse($html);
		$pdf->pageMargins = "23,15,26,15,9,9";
		$pdf->documentTitle = $this->cv->name;
		$pdf->documentAuthor = 'Source-Code.com';
		$pdf->outputName = $fileName;
		$pdf->outputDestination = \PdfResponse\PdfResponse::OUTPUT_FILE;
		$response = $this->presenter->context->getByType('Nette\Http\Response');
		$request = $this->presenter->context->getByType('Nette\Http\Request');
		$pdf->send($request, $response);
	}
}

interface ICvDocumentFactory
{

	/** @return CvDocument */
	function create();
}