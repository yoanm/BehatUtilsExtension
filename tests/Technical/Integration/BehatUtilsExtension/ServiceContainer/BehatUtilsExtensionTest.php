<?php
namespace Technical\Integration\Yoanm\BehatUtilsExtension\ServiceContainer;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractContainerBuilderTestCase;
use Prophecy\Argument;
use Prophecy\Argument\Token;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yoanm\BehatUtilsExtension\ServiceContainer\BehatUtilsExtension;

class BehatUtilsExtensionTest extends AbstractContainerBuilderTestCase
{
    /** @var BehatUtilsExtension */
    private $extension;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->extension = new BehatUtilsExtension();
    }

    public function testModulesConfigAppended()
    {
        $builder = new ArrayNodeDefinition('test');

        $this->extension->configure($builder);

        $config = (new Processor())->process($builder->getNode(true), []);

        $this->assertArrayHasKey('step_logger', $config);
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
        // Don't use default configuration as it can change
        $config = [
            'logger' => [
                'path' => 'my_path',
                'level' => 'my_level',
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
        // Assert LoggerAwareInitializer is present (means 'initializer.xml' has been loaded)
        $this->assertContains('behat_utils_extension.initializer.behat_subscriber', $serviceList);
        // Assert BehatStepLoggerSubscriber is not present (means 'behat_step_logger.xml' has not been loaded)
        $this->assertNotContains('behat_utils_extension.subscriber.behat_step', $serviceList);
    }

    public function testStepLoggerLoadedIfEnabled()
    {
        $container = $this->loadContainer(['step_logger' => ['enabled' => true]]);

        // Assert BehatStepLoggerSubscriber is present (means 'behat_step_logger.xml' has been loaded)
        $this->assertContains('behat_utils_extension.subscriber.behat_step', $container->getServiceIds());
    }

    /**
     * @param array $config Extension config
     *
     * @return ContainerBuilder
     */
    protected function loadContainer(array $config = null)
    {
        if (null == $config) {
            $config = [
                'logger' => [
                    'path' => 'my_path',
                    'level' => 'my_level',
                ],
                'step_logger' => [
                    'enabled' => false,
                ],
            ];
        }

        // Fake event_dispatcher
        $this->registerService('event_dispatcher', '\stdClass');

        $this->extension->load($this->container, $config);

        $this->compile();

        return $this->container;
    }
}
