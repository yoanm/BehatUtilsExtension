<?php
namespace UnitTest\Yoanm\BehatUtilsExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use Monolog\Logger;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\BehatUtilsExtension\Context\Initializer\LoggerAwareInitializer;
use Yoanm\BehatUtilsExtension\Context\LoggerAwareInterface;

/**
 * Class LoggerAwareInitializerTest
 */
class LoggerAwareInitializerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Logger|ObjectProphecy */
    private $logger;
    /** @var LoggerAwareInitializer */
    private $initializer;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->logger = $this->prophesize(Logger::class);

        $this->initializer = new LoggerAwareInitializer(
            $this->logger->reveal()
        );
    }

    public function testInitializeContextIfImplementInterface()
    {
        /** @var LoggerAwareInterface|ObjectProphecy $context */
        $context = $this->prophesize(LoggerAwareInterface::class);

        $context->setBehatLogger($this->logger->reveal())
            ->shouldBeCalledTimes(1);

        $this->initializer->initializeContext($context->reveal());
    }

    public function testInitializeContextIfNotImplementInterface()
    {
        /** @var Context|ObjectProphecy $context */
        $context = $this->prophesize(Context::class);

        $this->assertNull(
            $this->initializer->initializeContext($context->reveal())
        );
    }
}
