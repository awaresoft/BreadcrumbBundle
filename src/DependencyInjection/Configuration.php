<?php

namespace Awaresoft\BreadcrumbBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder
            ->root('awaresoft_breadcrumb')
            ->children()
                ->arrayNode('routes')
                    ->isRequired()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('controller')
                                ->isRequired()
                            ->end()
                            ->scalarNode('position')->end()
                            ->scalarNode('template')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('hidden_on_routes')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
