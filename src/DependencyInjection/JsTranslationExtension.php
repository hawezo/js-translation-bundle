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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Hawezo\JsTranslationBundle\DependencyInjection\Configuration;

/**
 * @author Enzo Innocenzi <enzo.inno@gmail.com>
 */
class JsTranslationExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yml');

        // There is surely a better way to retrieve this automatically? Right?
        $configuration = $this->getConfiguration($configs, $container);
        $nodes = $configuration->getConfigTreeBuilder()->buildTree()->getChildren();

        foreach ($nodes as $param => $node) {
            $this->setParameter($param, $configs[0], $node, $container);
        }
    }

    private function setParameter(string $param, array $configs, NodeInterface $node, ContainerBuilder $container)
    {
        $container->setParameter('js_translation.' . $param, $configs[$param] ?? $node->getDefaultValue());
    }

    public function getConfiguration(array $configs, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }
}