<?php
namespace Technical\Unit\Yoanm\BehatUtilsExtension\ServiceContainer;

use Prophecy\Argument;
use Prophecy\Argument\Token;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yoanm\BehatUtilsExtension\ServiceContainer\BehatUtilsExtension;

class BehatUtilsExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var BehatUtilsExtension */
    private $extension;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->extension = new BehatUtilsExtension();
    }

    public function testGetConfigKey()
    {
        $this->assertSame(
            BehatUtilsExtension::EXTENSION_CONFIG_KEY,
            $this->extension->getConfigKey()
        );
    }

    public function testProcess()
    {
        /** @var ContainerBuilder|ObjectProphecy $container */
        $container = $this->prophesize(ContainerBuilder::class);

        $this->assertNull($this->extension->process($container->reveal()));
    }
}
