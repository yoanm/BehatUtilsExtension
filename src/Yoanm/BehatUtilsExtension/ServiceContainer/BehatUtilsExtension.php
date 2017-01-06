<?php
namespace Yoanm\BehatUtilsExtension\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Yoanm\BehatUtilsExtension\ServiceContainer\Configuration\EventSubscriberConfiguration;
use Yoanm\BehatUtilsExtension\ServiceContainer\Configuration\LoggerConfiguration;
use Yoanm\BehatUtilsExtension\ServiceContainer\Configuration\StepLoggerConfiguration;

class BehatUtilsExtension implements Extension
{
    const EXTENSION_CONFIG_KEY = 'behat_utils';
    const CONTAINER_KEY_BASE = 'behat_utils_extension';

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return self::EXTENSION_CONFIG_KEY;
    }

    // @codeCoverageIgnoreStart
    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }
    // @codeCoverageIgnoreEnd

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder->append((new LoggerConfiguration())->getConfigNode());
        $builder->append((new StepLoggerConfiguration())->getConfigNode());
        $builder->append((new EventSubscriberConfiguration())->getConfigNode());
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->bindConfigToContainer($container, $config);

        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('logger.xml');

        if (true === $config['event_subscriber']['enabled']) {
            $loader->load('event_subscriber.xml');
        }

        if (true === $config['step_logger']['enabled']) {
            $loader->load('behat_step_logger.xml');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     * @param string           $baseId
     */
    protected function bindConfigToContainer(
        ContainerBuilder $container,
        array $config,
        $baseId = self::CONTAINER_KEY_BASE
    ) {
        foreach ($config as $configKey => $configValue) {
            if (is_array($configValue)) {
                $this->bindConfigToContainer(
                    $container,
                    $configValue,
                    sprintf('%s.%s', $baseId, $configKey)
                );
            } else {
                $container->setParameter(sprintf('%s.%s', $baseId, $configKey), $configValue);
            }
        }
    }
}
