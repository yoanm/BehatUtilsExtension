<?php
namespace Technical\Integration\Yoanm\BehatUtilsExtension\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractContainerBuilderTestCase;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ServiceContainerTestCase extends AbstractContainerBuilderTestCase
{
    /**
     * @return Extension
     */
    protected function getExtension()
    {
        throw new \Exception('You must override getExtension method to return your extension class');
    }

    /**
     * @return NodeInterface
     */
    protected function getConfigurationNode()
    {
        throw new \Exception('You must override getConfigurationNode method to return a configuration node interface');
    }

    /**
     * @param array $config Extension config
     *
     * @return ContainerBuilder
     */
    protected function loadContainer(array $config = [])
    {
        $this->getExtension()->load(
            $this->container,
            $this->normalizeConfig($config)
        );

        $this->compile();

        return $this->container;
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected function normalizeConfig(array $config = [])
    {
        return (new Processor())->process($this->getConfigurationNode(), [$config]);
    }
}
