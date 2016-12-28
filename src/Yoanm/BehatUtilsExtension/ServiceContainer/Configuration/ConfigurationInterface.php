<?php
namespace Yoanm\BehatUtilsExtension\ServiceContainer\Configuration;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;

interface ConfigurationInterface
{
    /**
     * @return NodeDefinition
     */
    public function getConfigNode();
}
