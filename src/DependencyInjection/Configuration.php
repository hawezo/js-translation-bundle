<?php

/*
 * This file is part of the JsTranslationBundle package.
 * 
 * © Enzo Innocenzi <enzo.inno@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Hawezo\JsTranslationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @author Enzo Innocenzi <enzo.inno@gmail.com>
 */
class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder('js_translation');
        $root = $tree->root('js_translation')
        // $tree->getRootNode()
            ->children()
                ->scalarNode('auto_extract')
                    ->defaultFalse()
                    ->end()
                ->scalarNode('translation_extract_path')
                    ->defaultValue('assets/js/messages.js')
                    ->end()
                ->arrayNode('export_locales')
                    ->info('The locales to be exported')
                        ->prototype('scalar')
                        ->defaultValue([])
                        ->end()
                    ->end()
                ->arrayNode('export_domains')
                    ->info('The domains to be exported')
                        ->prototype('scalar')
                        ->defaultValue([])
                        ->end()
                    ->end()
        ;
        return $tree;
    }
}