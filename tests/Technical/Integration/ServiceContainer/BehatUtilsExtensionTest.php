<?php
namespace Technical\Integration\Yoanm\BehatUtilsExtension\ServiceContainer;

use Prophecy\Argument;
use Prophecy\Argument\Token;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\Processor;
use Yoanm\BehatUtilsExtension\ServiceContainer\BehatUtilsExtension;

class BehatUtilsExtensionTest extends ServiceContainerTestCase
{
    /** @var BehatUtilsExtension */
    private $extension;
    /** @var NodeInterface */
    private $configurationNode;

    public function testModulesConfigAppended()
    {
        $builder = new ArrayNodeDefinition('test');
        $this->extension->configure($builder);
        $config = (new Processor())->process($builder->getNode(true), []);

        $this->assertArrayHasKey('step_logger', $config);
        $this->assertArrayHasKey('event_subscriber', $config);
        $this->assertArrayHasKey('logger', $config);
    }

    /**
     * @smokeTest
     * Will throw an exception if something goes wrong.
     * Like missing parameter, bad argument type, ...
     */
    public function testLoadable()
    {
        $this->assertNotEmpty($this->loadContainer());
    }

    public function testConfigurationBindedToContainerParameter()
    {
        // Don't use default configuration make assertions based on this array
        $config = [
            'logger' => [
                'path' => 'my_path',
                'level' => 'my_level',
            ],
            'event_subscriber' => [
                'enabled' => true,
            ],
            'step_logger' => [
                'enabled' => true,
            ],
        ];
        $container = $this->loadContainer($config);

        $this->assertSame(
            $config['logger']['path'],
            $container->getParameter('behat_utils_extension.logger.path')
        );
        $this->assertSame(
            $config['logger']['level'],
            $container->getParameter('behat_utils_extension.logger.level')
        );
        $this->assertSame(
            $config['event_subscriber']['enabled'],
            $container->getParameter('behat_utils_extension.event_subscriber.enabled')
        );
        $this->assertSame(
            $config['step_logger']['enabled'],
            $container->getParameter('behat_utils_extension.step_logger.enabled')
        );
    }

    public function testServiceLoaded()
    {
        $container = $this->loadContainer();

        $serviceList = $container->getServiceIds();

        // Assert Logger is present (means 'logger.xml' has been loaded)
        $this->assertContains('behat_utils_extension.logger', $serviceList);
        // Assert BehatContextSubscriberInitializer is not present (means 'event_subscriber.xml' has not been loaded)
        $this->assertNotContains('behat_utils_extension.initializer.behat_subscriber', $serviceList);
        // Assert BehatStepLoggerSubscriber is not present (means 'behat_step_logger.xml' has not been loaded)
        $this->assertNotContains('behat_utils_extension.subscriber.behat_step', $serviceList);
    }

    /**
     * @group yo
     */
    public function testBehatSubscriberLoadedIfEnabled()
    {
        $container = $this->loadContainer(['event_subscriber' => ['enabled' => true]]);

        // Assert BehatContextSubscriberInitializer is present (means 'event_subscriber.xml' has been loaded)
        $this->assertContains('behat_utils_extension.initializer.behat_subscriber', $container->getServiceIds());
    }

    public function testStepLoggerLoadedIfEnabled()
    {
        $container = $this->loadContainer(['step_logger' => ['enabled' => true]]);

        // Assert BehatStepLoggerSubscriber is present (means 'behat_step_logger.xml' has been loaded)
        $this->assertContains('behat_utils_extension.subscriber.behat_step', $container->getServiceIds());
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->extension = new BehatUtilsExtension();
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtension()
    {
        return $this->extension;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigurationNode()
    {
        return $this->configurationNode;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadContainer(array $config = [])
    {
        // Fake event_dispatcher
        $this->registerService('event_dispatcher', \stdClass::class);

        return parent::loadContainer($config);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig()
    {
        return [
            'logger' => [
                'path' => 'path',
                'level' => 'level',
            ],
            'event_subscriber' => [
                'enabled' => false,
            ],
            'step_logger' => [
                'enabled' => false,
            ],
        ];
    }
}
