<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;

class Configuration extends SiteAccessConfiguration
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('ez_publish_legacy');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->booleanNode('clear_all_spi_cache_on_symfony_clear_cache')->defaultTrue()->end()
                ->booleanNode('clear_all_spi_cache_from_legacy')->defaultTrue()->end()
                ->scalarNode('root_dir')
                    ->validate()
                        ->ifTrue(
                            static function ($v) {
                                return !file_exists($v);
                            }
                        )
                        ->thenInvalid("Provided eZ Publish Legacy root dir does not exist!'")
                    ->end()
                ->end()
                ->arrayNode('legacy_aware_routes')
                    ->prototype('scalar')->end()
                    ->info('Routes that are allowed when legacy_mode is true. Must be routes identifiers (e.g. "my_route_name"). Can be a prefix, so that all routes beginning with given prefix will be taken into account.')
                ->end()
            ->end();

        $this->addSiteAccessSettings($this->generateScopeBaseNode($rootNode));

        return $treeBuilder;
    }

    private function addSiteAccessSettings(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('templating')
                ->children()
                    ->scalarNode('view_layout')
                        ->info('Template reference to use as pagelayout while rendering a content view in legacy')
                        ->example('@eZDemo/pagelayout.html.twig')
                    ->end()
                    ->scalarNode('module_layout')
                        ->info('Template reference to use as pagelayout for legacy modules. If not specified, pagelayout from legacy will be used.')
                    ->end()
                ->end()
            ->end()
            ->booleanNode('legacy_mode')
                ->info('Whether to use legacy mode or not. If true, will let the legacy kernel handle url aliases.')
            ->end();
    }
}
