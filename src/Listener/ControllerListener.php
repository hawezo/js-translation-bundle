<?php

/*
 * This file is part of the JsTranslationBundle package.
 * 
 * © Enzo Innocenzi <enzo.inno@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Hawezo\JsTranslationBundle\Listener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Hawezo\JsTranslationBundle\Command\JsTranslationExportCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Catches the CONTROLLER event to automatically extract the Javascript translation file.
 * 
 * @author Enzo Innocenzi <enzo.inno@gmail.com>
 */
class ControllerListener
{
    protected $autoExtract;
    protected $kernelInterface;

    public function __construct(ContainerBagInterface $container, KernelInterface $kernel)
    {
        $this->autoExtract = $container->get('js_translation.auto_extract');
        $this->kernelInterface = $kernel;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!$event->isMasterRequest() || !$this->autoExtract) {
            return;
        }
        
        $application = new Application($this->kernelInterface);
        $application->setAutoExit(false);

        $input = new ArrayInput([ 'command' => JsTranslationExportCommand::getDefaultName() ]);
        $application->run($input, new NullOutput());
    }
}