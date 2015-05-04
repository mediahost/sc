<?php

namespace App\Extensions;

use App\Model\Entity\Cv;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Latte\Engine;
use Nette\Object;
use Nette\Utils\FileSystem;
use PdfResponse\PdfResponse;

class CvToPdf extends Object
{

	/** @var string */
	private $rootFolder;
	
	/** @var IRequest @inject */
	public $request;
	
	/** @var IResponse @inject */
	public $response;

	public function setFolder($folder)
	{
		$this->rootFolder = $folder;
		FileSystem::createDir($folder);

		return $this;
	}

	/** Save Cv entity to PDF */
	public function save(Cv $cv)
	{
		$folder = $this->rootFolder . '/' . $cv->pdfFolder;
		FileSystem::createDir($folder);
		$pathToFile = $folder . '/' . $cv->pdfFilename;
		
		$latte = new Engine;
		$templatePath = realpath(__DIR__ . '/templates/' . 'template1.latte');
		$html = $latte->renderToString($templatePath, ['cv' => $cv]);
//		$template = new \Nette\Templating\Template();
		$source = "<b>{$cv->name}</b>";
		
		$pdf = new PdfResponse($html);
		
		$pdf->documentTitle = $cv->name;
		$pdf->documentAuthor = 'Source-Code.com';
		$pdf->outputDestination = PdfResponse::OUTPUT_FILE;
		$pdf->outputName = $pathToFile;
		
		$pdf->send($this->request, $this->response);
	}

}
