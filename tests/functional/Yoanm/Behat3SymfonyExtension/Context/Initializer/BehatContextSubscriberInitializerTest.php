<?php
namespace FunctionalTest\Yoanm\BehatUtilsExtension\Context\Initializer;

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
}
