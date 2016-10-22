<?php

namespace App\Mail\Messages;

use App\Extensions\Settings\SettingsStorage;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Translation\Translator;
use Latte\Engine;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\ITemplateFactory;
use Nette\Http\Request;
use Nette\Mail\IMailer;
use Nette\Mail\Message;

abstract class BaseMessage extends Message
{

	/** @var IMailer @inject */
	public $mailer;

	/** @var ITemplateFactory @inject */
	public $templateFactory;

	/** @var LinkGenerator @inject */
	public $linkGenerator;

	/** @var SettingsStorage @inject */
	public $settings;

	/** @var Request @inject */
	public $httpRequest;

	/** @var Translator @inject */
	public $translator;

	/** @var EntityManager @inject */
	public $em;

	/** @var array */
	protected $params = [];

	/** @var bool */
	protected $isNewsletter = FALSE;

	/** @var string */
	protected $unsubscribeLink;

	/**
	 * @return string
	 */
	protected function getPath()
	{
		$name = $this->reflection->getShortName();
		return __DIR__ . '/' . $name . '/' . $name . '.latte';
	}

	protected function build()
	{
		$this->params += [
			'pageInfo' => $this->settings->pageInfo,
			'mail' => $this,
			'colon' => '',
			'isNewsletter' => $this->isNewsletter,
			'unsubscribeLink' => $this->unsubscribeLink ? $this->unsubscribeLink : $this->httpRequest->url->hostUrl,
			'locale' => $this->translator->getLocale(),
			'hostUrl' => $this->httpRequest->url->hostUrl,
			'basePath' => $this->httpRequest->url->basePath,
		];

		$template = $this->templateFactory->createTemplate();
		$template->setTranslator($this->translator)
			->setFile($this->getPath())
			->setParameters($this->params)
			->_control = $this->linkGenerator;

		$this->setHtmlBody($template);

		return parent::build();
	}

	public function addTo($email, $name = NULL)
	{
		if (is_array($email) || $email instanceof ArrayHash) {
			foreach ($email as $mail) {
				parent::addTo($mail);
			}
		} else {
			parent::addTo($email, $name);
		}
		return $this;
	}

	protected function beforeSend()
	{

	}

	protected function afterSend()
	{

	}

	public function send()
	{
		$this->beforeSend();
		$this->mailer->send($this);
		$this->afterSend();
	}

	// <editor-fold defaultstate="collapsed" desc="setters">

	public function addParameter($paramName, $value)
	{
		$this->params[$paramName] = $value;
	}

	public function setNewsletter($unsubscribeLink = NULL)
	{
		$this->isNewsletter = TRUE;
		$this->unsubscribeLink = $unsubscribeLink;

		return $this;
	}

	// </editor-fold>

}
