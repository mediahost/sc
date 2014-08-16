<?php

namespace App\BaseModule\Presenters;

use Nette;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    /** @var \Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory @inject */
    public $formFactoryFactory;

    /** @var \WebLoader\LoaderFactory @inject */
    public $webLoader;
	
	/** @var \App\Components\Sign\ISignOutControlFactory @inject */
	public $iSignOutControlFactory;

    /** @var string */
    private $lang;

	
    protected function startup()
    {
        parent::startup();

        $this->_setLang();
    }
    
	public function checkRequirements($element)
    {     
        $secured = $element->getAnnotation('secured');
        $resource = $element->getAnnotation('resource');
        $privilege = $element->getAnnotation('privilege');

        if ($secured) {
            if (!$this->user->isLoggedIn()) {
                $this->redirect(':Front:Sign:in', array('backlink' => $this->storeRequest()));
                $this->flashMessage('You should be logged in!');
            } elseif (!$this->user->isAllowed($resource, $privilege)) {
                throw new Nette\Application\ForbiddenRequestException;
            }
        }
    }
    
    

    protected function beforeRender()
    {
        parent::beforeRender();

        $this->template->lang = $this->lang;
    }


// <editor-fold defaultstate="collapsed" desc="Components">
	
	/** @return \App\Components\Sign\SignOutControl */
	public function createComponentSignOut()
    {
        return $this->iSignOutControlFactory->create();
    }

// </editor-fold>
	
// <editor-fold defaultstate="collapsed" desc="lang">
    private function _setLang()
    {
        $this->lang = "en";
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="css webloader">

    /** @return CssLoader */
    protected function createComponentCssFront()
    {
        $css = $this->webLoader->createCssLoader('front')
                ->setMedia('screen,projection,tv');
        return $css;
    }

    /** @return CssLoader */
    protected function createComponentCssAdmin()
    {
        $css = $this->webLoader->createCssLoader('admin')
                ->setMedia('screen,projection,tv');
        return $css;
    }

    /** @return CssLoader */
    protected function createComponentCssMetronicCore()
    {
        $css = $this->webLoader->createCssLoader('metronicCore')
                ->setMedia('screen,projection,tv');
        return $css;
    }

    /** @return CssLoader */
    protected function createComponentCssMetronicPlugin()
    {
        $css = $this->webLoader->createCssLoader('metronicPlugin')
                ->setMedia('screen,projection,tv');
        return $css;
    }

    /** @return CssLoader */
    protected function createComponentCssMetronicTheme()
    {
        $css = $this->webLoader->createCssLoader('metronicTheme')
                ->setMedia('screen,projection,tv');
        return $css;
    }

    /** @return CssLoader */
    protected function createComponentCssPrint()
    {
        $css = $this->webLoader->createCssLoader('print')
                ->setMedia('print');
        return $css;
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="js webloader">

    /** @return JavaScriptLoader */
    protected function createComponentJsApp()
    {
        return $this->webLoader->createJavaScriptLoader('app');
    }

    /** @return JavaScriptLoader */
    protected function createComponentJsAppPlugins()
    {
        return $this->webLoader->createJavaScriptLoader('appPlugins');
    }

    /** @return JavaScriptLoader */
    protected function createComponentJsMetronicPlugins()
    {
        return $this->webLoader->createJavaScriptLoader('metronicPlugins');
    }

    /** @return JavaScriptLoader */
    protected function createComponentJsMetronicCore()
    {
        return $this->webLoader->createJavaScriptLoader('metronicCore');
    }

    /** @return JavaScriptLoader */
    protected function createComponentJsMetronicCoreIE9()
    {
        return $this->webLoader->createJavaScriptLoader('metronicCoreIE9');
    }

// </editor-fold>
}
