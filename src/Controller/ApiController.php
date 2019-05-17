<?php

/*
 * This file is part of the JsTranslationBundle package.
 * 
 * © Enzo Innocenzi <enzo.inno@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Hawezo\JsTranslationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Hawezo\JsTranslationBundle\Extractor\ExtractorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @author Enzo Innocenzi <enzo.inno@gmail.com>
 */
class ApiController extends AbstractController
{
    private $extractor;

    public function __construct(ExtractorInterface $extractor)
    {
        $this->extractor = $extractor;
    }

    public function getTranslations(Request $request)
    {
        $domains = [ $request->query->get('domain', null) ];
        $locales = [ $request->query->get('locale', $request->getLocale()) ];

        $translations = $this->extractor->extract($domains, $locales);

        return new JsonResponse($translations);
    }

}