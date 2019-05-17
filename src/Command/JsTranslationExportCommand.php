<?php

/*
 * This file is part of the JsTranslationBundle package.
 * 
 * © Enzo Innocenzi <enzo.inno@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Hawezo\JsTranslationBundle\Command;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Hawezo\JsTranslationBundle\Extractor\ExtractorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/**
 * @author Enzo Innocenzi <enzo.inno@gmail.com>
 */
class JsTranslationExportCommand extends Command
{
    const PRETTY_JS = 'pretty-js';

    protected static $defaultName = 'translation:extract-js';

    private $extractor;
    private $root;
    private $export;
    private $domains;
    private $locales;

    public function __construct(ExtractorInterface $extractor, ContainerBagInterface $params)
    {
        parent::__construct();

        $this->extractor = $extractor;
        $this->root = $params->get('kernel.project_dir');
        $this->export = $params->get('js_translation.translation_extract_path');
        $this->locales = $params->get('js_translation.export_locales');
        $this->domains = $params->get('js_translation.export_domains');
    }

    protected function configure()
    {
        $this
            ->setDescription('Exports the Javascript translation file')
            ->addOption(self::PRETTY_JS, 'p', InputOption::VALUE_NONE, 'Prettifies the exported Javascript.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // Options
        $prettify = $input->getOption(self::PRETTY_JS);
        $locales = $this->locales;
        $domains = $this->domains;

        $translations = $this->extractor->extract($domains, $locales);

        // @todo - Twig export
        $javascript = $this->getJavascript($translations, $prettify);
        
        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            $this->root . '\\' . $this->export, 
            $javascript);

        $io->success(sprintf('Translations messages have been written to %s.', $this->export));
    }

    protected function getJavascript(array $translations, bool $prettify = false)
    {
        $str = json_encode($translations, $prettify ? JSON_PRETTY_PRINT : 0);
        return sprintf(
            'export default %s;',
            $str
        );
    }
}