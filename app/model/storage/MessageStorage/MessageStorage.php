<?php

namespace App\Model\Storage;

use Nette\Bridges\ApplicationLatte\Template,
	Nette\Mail\Message;


/**
 * Description of MessageStorage
 *
 * @author Martin Šifra <me@martinsifra.cz>
 */
class MessageStorage extends \Nette\Object
{
	// <editor-fold defaultstate="collapsed" desc="constants & variables">
	// </editor-fold>

	/**
	 *
	 * @param Template $template
	 * @param type $filename
	 * @param type $parameters
	 * @return Message
	 */
	public function getMessage(Template $template, $filename, $parameters)
	{
		$template->setFile(__DIR__ .'/'. $filename . '.latte')
				->setParameters($parameters);

		$message = new Message();
		$message->setHtmlBody($template);

		return $message;
	}

	public function getRegistrationMail(Template $template, $parameters)
	{
		$message = $this->getMessage($template, 'registration', $parameters);
		return $message->setFrom('noreply@sc.com');
	}

	public function getForgottenMail(Template $template, $parameters)
	{
		$message = $this->getMessage($template, 'forgotten', $parameters);
		return $message->setFrom('noreply@sc.com');
	}

}
