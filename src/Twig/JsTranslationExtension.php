<?php

namespace Hawezo\JsTranslationBundle\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Hawezo\JsTranslationBundle\Extractor\ExtractorInterface;

/**
 * @author Enzo Innocenzi <enzo.inno@gmail.com>
 */
class JsTranslationExtension extends \Twig_Extension
{
    const META_NAME_URL =       'translation-url';
    const META_NAME_CONTENT =   'translation';
    const META_NAME_SETTINGS =  'translation-settings';

    const DOMAINS = 'domains';

    private $extractor;
    private $router;

    public function __construct(ExtractorInterface $extractor, UrlGeneratorInterface $router)
    {
        $this->extractor = $extractor;
        $this->router = $router;
    }

    public function getFunctions()
    {
        return [
            new \Twig_Function('js_translation_meta',       [ $this, 'content' ],   [ 'is_safe' => [ 'html' ]]),
            new \Twig_Function('js_translation_meta_url',   [ $this, 'url'  ],      [ 'is_safe' => [ 'html' ]]),
        ];
    }

    /**
     * Prints a meta tag containing the full translations.
     * 
     * @param array $settings
     * 
     * @return string
     */
    public function content(array $settings = [])
    {
        $settings = $this->filterSettings($settings);
        $translations = htmlspecialchars(json_encode($this->extractor->extract($settings[self::DOMAINS] ?? [])));

        return $this->getMeta(self::META_NAME_CONTENT, $translations, $settings);
    }

    /**
     * Prints a meta tag containing the url to the controller that will return the full translations.
     * 
     * @param array $settings
     * 
     * @return string
     */
    public function url(array $settings = [])
    {
        $settings = $this->filterSettings($settings);
        $url = $this->router->generate('js_translation_api', $settings);

        return $this->getMeta(self::META_NAME_URL, $url, $settings);
    }

    /**
     * Returns both required meta tags.
     * 
     * @param string $type The type of meta tag. Either an URL or an ARRAY.
     * @param string $content The content of the meta tag.
     * @param array $settings The settings.
     * 
     * @return string
     */
    private function getMeta(string $type, string $content, array $settings = []) 
    {
        $meta = sprintf('<meta name="%s" content="%s" />', $type, $content);
        $meta .= sprintf('%s<meta name="%s" content="%s" />', PHP_EOL, self::META_NAME_SETTINGS, $this->formatSettings($settings));

        return $meta;
    }

    /**
     * Encodes the settings.
     * 
     * @return string
     */
    private function formatSettings(array $settings = [])
    {
        return htmlspecialchars(json_encode($this->filterSettings($settings)));
    }

    /**
     * Filters the settings with only the known ones.
     * 
     * @param array $settings
     * 
     * @return array
     */
    private function filterSettings(array $settings = [])
    {
        $_settings = [];
        $_keys = [ self::DOMAINS ];

        foreach ($settings as $key => $value) {
            if (!in_array($key, $_keys)) {
                @trigger_error(sprintf('%s is not a valid setting.', $key));
                continue;
            }

            $_settings[$key] = $value;
        }

        return $_settings;
    }
}