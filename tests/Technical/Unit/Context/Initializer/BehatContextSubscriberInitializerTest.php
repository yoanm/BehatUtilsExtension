<?php
namespace Technical\Unit\Yoanm\BehatUtilsExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Yoanm\BehatUtilsExtension\Context\BehatContextSubscriberInterface;
use Yoanm\BehatUtilsExtension\Context\Initializer\BehatContextSubscriberInitializer;

/**
 * Class BehatContextSubscriberInitializerTest
 */
class BehatContextSubscriberInitializerTest extends \PHPUnit_Framework_TestCase
{
    /** @var EventDispatcherInterface|ObjectProphecy */
    private $behatEventDispatcher;
    /** @var BehatContextSubscriberInitializer */
    private $initializer;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->behatEventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->initializer = new BehatContextSubscriberInitializer(
            $this->behatEventDispatcher->reveal()
        );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [
                ScenarioTested::AFTER => 'clearBehatContextSubscriber',
                ExampleTested::AFTER => 'clearBehatContextSubscriber',
            ],
            BehatContextSubscriberInitializer::getSubscribedEvents()
        );
    }

    /**
     * Will not pass the the context to dispatcher
     * @SmokeTest
     */
    public function testInitializeContextIfNotImplementInterface()
    {
        /** @var Context|ObjectProphecy $context */
        $context = $this->prophesize(Context::class);

        $this->behatEventDispatcher->addSubscriber(Argument::cetera())
            ->shouldNotBeCalled();

        $this->initializer->initializeContext($context->reveal());
    }

    /**
     * Will pass the the context to dispatcher
     */
    public function testInitializeContextIfImplementInterface()
    {
        /** @var BehatContextSubscriberInterface|ObjectProphecy $context */
        $context = $this->prophesize(BehatContextSubscriberInterface::class);

        $this->behatEventDispatcher->addSubscriber($context)
            ->shouldBeCalledTimes(1);

        $this->initializer->initializeContext($context->reveal());
    }

    /**
     * Will detach contexts from dispatcher, only for contexts that have been initialized
     */
    public function testClearBehatContextSubscriber()
    {
        /** @var BehatContextSubscriberInterface|ObjectProphecy $context */
        $context = $this->prophesize(BehatContextSubscriberInterface::class);

        $this->initializer->initializeContext($context->reveal());

        $this->behatEventDispatcher->removeSubscriber($context->reveal())
            ->shouldBeCalled();

        $this->initializer->clearBehatContextSubscriber();
    }
}
