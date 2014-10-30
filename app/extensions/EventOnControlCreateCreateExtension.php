<?php

namespace App\Extensions;

use Nette\DI\CompilerExtension;

class EventOnControlCreateCreateExtension extends CompilerExtension
{

    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();
        $definitions = $builder->getDefinitions();
		
		foreach ($definitions as $definition) {
			if ($definition->implement && method_exists($definition->implement, 'create')) {
				$definition->addSetup('?->createEvent(?)->dispatch($service);', [
					'@Kdyby\Events\EventManager',
					'Nette\Application\UI\Control::onCreate'
				]);
			}
		}
    }

}
