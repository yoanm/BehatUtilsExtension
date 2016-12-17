<?php
namespace Yoanm\BehatUtilsExtension\ServiceContainer\Configuration;

use Monolog\Logger;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class LoggerConfiguration
{
    /**
     * @return NodeDefinition
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('logger');
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('path')
                    ->info('Name of the log file')
                    ->defaultValue('behat.log')
                ->end()
                ->scalarNode('level')
                    ->info('Log level')
                    ->beforeNormalization()
                        ->always()
                        ->then(function ($value) {
                            return Logger::toMonologLevel($value);
                        })
                    ->end()
                    ->defaultValue(Logger::INFO)
                ->end()
            ->end();

        return $rootNode;
    }
}
