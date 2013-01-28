<?php

namespace Liip\OneallBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder,
    Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('liip_oneall');

        $rootNode
            ->fixXmlConfig('permission', 'permissions')
            ->children()
                ->scalarNode('site_subdomain')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('site_public_key')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('site_private_key')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('callback_path')->defaultNull()->end()
                ->scalarNode('default_firewall_name')->defaultNull()->end()
                ->scalarNode('alias')->defaultNull()->end()
                ->arrayNode('class')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('api')->defaultValue('Liip\OneallBundle\Oneall\OneallApi')->end()
                        ->scalarNode('twig')->defaultValue('Liip\OneallBundle\Twig\Extension\OneallExtension')->end()
                    ->end()
                ->end()
                ->arrayNode('social_links')->prototype('scalar')->end()
            ->end();

        return $treeBuilder;
    }
}
