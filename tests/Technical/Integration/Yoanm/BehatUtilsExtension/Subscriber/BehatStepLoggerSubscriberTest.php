<?php
namespace Technical\Integration\Yoanm\BehatUtilsExtension\Subscriber;

use Behat\Behat\EventDispatcher\Event\BackgroundTested;
use Behat\Behat\EventDispatcher\Event\BeforeFeatureTested;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\EventDispatcher\Event\GherkinNodeTested;
use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\NodeInterface;
use Behat\Gherkin\Node\StepContainerInterface;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\StaticEnvironment;
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Behat\Testwork\Suite\GenericSuite;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Yoanm\BehatUtilsExtension\Subscriber\BehatStepLoggerSubscriber;
use Yoanm\BehatUtilsExtension\Subscriber\ListenerPriority;
use Behat\Testwork\Environment\Environment;

/**
 * Class BehatStepLoggerSubscriberTest
 */
class BehatStepLoggerSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /** @var LoggerInterface|ObjectProphecy */
    private $logger;
    /** @var BehatStepLoggerSubscriber */
    private $subscriber;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->subscriber = new BehatStepLoggerSubscriber(
            $this->logger->reveal()
        );
    }

    public function testGetSubscribedEvents()
    {
        // Use high priority to log event at beginning
        $expectedListener = ['logEvent', ListenerPriority::HIGH_PRIORITY];

        $this->assertSame(
            [
                FeatureTested::BEFORE => $expectedListener,
                BackgroundTested::BEFORE => $expectedListener,
                ScenarioTested::BEFORE => $expectedListener,
                OutlineTested::BEFORE => $expectedListener,
                ExampleTested::BEFORE => $expectedListener,
                StepTested::BEFORE => $expectedListener,

                FeatureTested::AFTER => $expectedListener,
                BackgroundTested::AFTER => $expectedListener,
                ScenarioTested::AFTER => $expectedListener,
                OutlineTested::AFTER => $expectedListener,
                ExampleTested::AFTER => $expectedListener,
                StepTested::AFTER => $expectedListener,
            ],
            BehatStepLoggerSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @dataProvider provideExpectedLogMessage
     * @param GherkinNodeTested $event
     * @param string            $expectedMessage
     */
    public function assertLogMessage(GherkinNodeTested $event, $expectedMessage)
    {
        $this->logger->debug($expectedMessage)->shouldBeCalled();

        $this->subscriber->logEvent($event);
    }

    /**
     * @return array
     */
    public function provideExpectedLogMessage()
    {
        return [
            'FeatureTested::BEFORE' => [
                'event' => new BeforeFeatureTested(
                    new StaticEnvironment(new GenericSuite('suite', [])),
                    new FeatureNode('title', 'description', [], null, [], 'keyword', 'language',' file', 'line')
                ),
                'expectedMEssage' => sprintf(
                    '[%]'
                ),
            ],
        ];
    }
}
