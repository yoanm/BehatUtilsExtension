<?php
namespace Technical\Integration\Yoanm\BehatUtilsExtension\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractContainerBuilderTestCase;
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
     * @return array
     */
    protected function getDefaultConfig()
    {
        throw new \Exception('You must override getDefaultConfig method to return the default config tree');
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
        return array_replace_recursive(
            $this->getDefaultConfig(),
            $config
        );
    }
}
