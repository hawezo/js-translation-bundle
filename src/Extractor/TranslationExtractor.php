<?php

/*
 * This file is part of the JsTranslationBundle package.
 * 
 * © Enzo Innocenzi <enzo.inno@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Hawezo\JsTranslationBundle\Extractor;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @author Enzo Innocenzi <enzo.inno@gmail.com>
 */
class TranslationExtractor implements ExtractorInterface
{

    protected $translator;
    protected $params;

    public function __construct(TranslatorInterface $translator, ParameterBagInterface $params)
    {
        $this->translator = $translator;
        $this->params = $params;
    }

    /**
     * @inheritdoc
     */
    public function extract($domains = null, $locales = null): array
    {
        if (!is_array($locales)) {
            $locales = array_filter([ $locales ]);
        }

        if (empty($locales)) {
            $locales = 
                array_unique(
                    array_merge(
                        [ $this->translator->getLocale() ],
                        $this->translator->getFallbackLocales()
                    )
                );
        }

        $paramsLocales = $this->params->get('js_translation.export_locales');
        $paramsDomains = $this->params->get('js_translation.export_domains');

        if (!empty($paramsLocales)) {
            $locales = array_intersect_key($paramsLocales, $locales);
        }

        if (!is_array($domains)) {
            $domains = array_filter([ $domains ]);
        }

        $translations = [];

        foreach ($locales as $_locale) {
            if (empty($_locale)) {
                continue;
            }

            $catalogue = $this->translator->getCatalogue($_locale);
            $_domains = empty($domains) ? $catalogue->getDomains() : array_intersect_key($catalogue->getDomains(), $domains);

            if (!empty($paramsDomains)) {
                $_domains = array_intersect_key($paramsDomains, $_domains);
            }

            foreach ($_domains as $_domain) {
                $translations[$_locale][$_domain] = $catalogue->all($_domain);
            }
        }

        return $translations;
    }

}