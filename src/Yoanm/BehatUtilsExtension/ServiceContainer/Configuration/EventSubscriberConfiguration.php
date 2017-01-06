<?php
namespace Yoanm\BehatUtilsExtension\ServiceContainer\Configuration;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class EventSubscriberConfiguration implements ConfigurationInterface
{
    /**
     * @return NodeDefinition
     */
    public function getConfigNode()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('event_subscriber');
        $rootNode
            ->addDefaultsIfNotSet()
            ->treatFalseLike(array('enabled' => false))
            ->treatNullLike(array('enabled' => false))
            ->treatTrueLike(array('enabled' => true))
            ->children()
                ->booleanNode('enabled')
                    ->defaultFalse()
        ;

        return $rootNode;
    }
}
