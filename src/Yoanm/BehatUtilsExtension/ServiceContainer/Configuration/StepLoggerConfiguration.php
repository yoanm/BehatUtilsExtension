<?php
namespace Yoanm\BehatUtilsExtension\ServiceContainer\Configuration;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class StepLoggerConfiguration
{
    /**
     * @return NodeDefinition
     */
    public function getConfigTreeBuilder()
    {
        $castToBool = function ($value) {
            $filtered = filter_var(
                $value,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );

            return (null === $filtered) ? (bool) $value : $filtered;
        };

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('step_logger');
        $rootNode
            ->addDefaultsIfNotSet()
            ->treatFalseLike(array('enabled' => false))
            ->treatNullLike(array('enabled' => false))
            ->treatTrueLike(array('enabled' => true))
            ->children()
                ->booleanNode('enabled')
                    ->beforeNormalization()
                        ->always()
                        ->then($castToBool)
                    ->end()
                    ->defaultFalse()
        ;

        return $rootNode;
    }
}
