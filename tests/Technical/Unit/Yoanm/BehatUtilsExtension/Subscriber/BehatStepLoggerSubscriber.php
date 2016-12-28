<?php
namespace Technical\Unit\Yoanm\BehatUtilsExtension\Subscriber;

use Behat\Behat\EventDispatcher\Event\BackgroundTested;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\EventDispatcher\Event\GherkinNodeTested;
use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\NodeInterface;
use Behat\Gherkin\Node\StepContainerInterface;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Yoanm\BehatUtilsExtension\Subscriber\BehatStepLoggerSubscriber;
use Yoanm\BehatUtilsExtension\Subscriber\ListenerPriority;

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
     * @SmokeTest
     */
    public function testLogEvent()
    {
        /** @var GherkinNodeTested|ObjectProphecy $event */
        $event = $this->prophesize(GherkinNodeTested::class);

        /*list($header, $context) = */$this->prophesizeProcessNodeEvent($event);
        //$this->logger->debug($header, $context);

        $this->subscriber->logEvent($event);
    }

    /**
     * @param GherkinNodeTested|ObjectProphecy|null $eventOrStubIt
     *
     * @return array
     */
    protected function prophesizeProcessNodeEvent(ObjectProphecy $eventOrStubIt = null)
    {
        list($context, $nodeHeader) = $this->prophesizeResolveContextAndNodeHeader($eventOrStubIt);

        return [
            sprintf(
                '[%s][%s]',
                $nodeHeader,
                $this->prophesizeResolveActionType($eventOrStubIt)
            ),
            $context
        ];
    }

    /**
     * @param GherkinNodeTested|ObjectProphecy|null $eventOrStubIt
     * @param NodeInterface|ObjectProphecy|null     $nodeOrStubIt
     * @param int|null                              $expectedLineOrStubIt
     * @param array|null                            $stepListOrStubIt
     *
     * @return array
     */
    protected function prophesizeResolveContextAndNodeHeader(
        ObjectProphecy $eventOrStubIt = null,
        ObjectProphecy $nodeOrStubIt = null,
        $expectedLineOrStubIt = null,
        array $stepListOrStubIt = null
    ) {
        /** @var StepTested|ObjectProphecy $event */
        $event = null === $eventOrStubIt
            ? $this->prophesize(StepTested::class) // Must be one of the switch class to avoid throwing exception
            : $eventOrStubIt;
        $context = [];
        switch (true) {
            case $event->reveal() instanceof StepTested:
                /** @var StepTested|ObjectProphecy $event $event */
                $nodeHeader = BehatStepLoggerSubscriber::HEADER_NODE_STEP;
                $context['text'] = $event->getStep()->getText();
                break;
            case $event->reveal() instanceof BackgroundTested:
                /** @var BackgroundTested|ObjectProphecy $event $event */
                $nodeHeader = BehatStepLoggerSubscriber::HEADER_NODE_BACKGROUND;
                $context['title'] = $event->getBackground()->getTitle();
                break;
            case $event->reveal() instanceof ScenarioTested:
                /** @var ScenarioTested|ObjectProphecy $event $event */
                $scenario = $event->getScenario();
                $nodeHeader = BehatStepLoggerSubscriber::HEADER_NODE_SCENARIO;
                if ($scenario instanceof ExampleNode) {
                    $nodeHeader = BehatStepLoggerSubscriber::HEADER_NODE_EXAMPLE;
                    $context['tokens'] = $scenario->getTokens();
                }
                $context['title'] = $scenario->getTitle();
                break;
            case $event->reveal() instanceof OutlineTested:
                /** @var OutlineTested|ObjectProphecy $event $event */
                $nodeHeader = BehatStepLoggerSubscriber::HEADER_NODE_OUTLINE;
                $context['title'] = $event->getOutline()->getTitle();
                break;
            case $event instanceof FeatureTested:
                /** @var FeatureTested|ObjectProphecy $event $event */
                $nodeHeader = BehatStepLoggerSubscriber::HEADER_NODE_FEATURE;
                $context['title'] = $event->getFeature()->getTitle();
                $context['file'] = $event->getFeature()->getFile();
                break;
            default:
                throw new \InvalidArgumentException(sprintf('event type not handled !'));
        }

        if (!$event->reveal() instanceof FeatureTested) {
            $context['line'] = $this->prophesizeResolveNodeLine(
                $eventOrStubIt,
                $nodeOrStubIt,
                $expectedLineOrStubIt,
                $stepListOrStubIt
            );
        }

        return [
            $context,
            $nodeHeader
        ];
    }

    /**
     * @param GherkinNodeTested|ObjectProphecy|null $eventOrStubIt
     * @param NodeInterface|ObjectProphecy|null     $nodeOrStubIt
     * @param int|null                              $expectedLineOrStubIt
     * @param array|null                            $stepListOrStubIt
     */
    protected function prophesizeResolveNodeLine(
        ObjectProphecy $eventOrStubIt = null,
        ObjectProphecy $nodeOrStubIt = null,
        $expectedLineOrStubIt = null,
        array $stepListOrStubIt = null
    ) {
        /** @var NodeInterface|ObjectProphecy $node */
        $event = null === $eventOrStubIt ? $this->prophesize(NodeInterface::class) : $eventOrStubIt;
        /** @var GherkinNodeTested|ObjectProphecy $event */
        $node = null === $nodeOrStubIt ? $this->prophesize(NodeInterface::class) : $nodeOrStubIt;

        // Required call returned value
        $event->getNode()->willReturn($node->reveal())->shouldBeCalled();

        if (null !== $nodeOrStubIt
            && null !== $expectedLineOrStubIt
        ) {
            $node->getLine()->willReturn($expectedLineOrStubIt)->shouldBeCalled();
        }

        if (null !== $nodeOrStubIt
            && null != $stepListOrStubIt
            && $event->reveal() instanceof AfterTested
            && !$event->reveal() instanceof StepTested /* no need to process step event end line */
            && $node->reveal() instanceof StepContainerInterface
        ) {
            /** @var StepContainerInterface|ObjectProphecy $node */
            $node->getSteps()->willReturn($stepListOrStubIt)->shouldBeCalled();

            $originalStepList = $stepListOrStubIt;
            $lastStep = array_pop($originalStepList);

            if ($expectedLineOrStubIt
                && $lastStep
                && $lastStep->reveal() instanceof StepNode
            ) {
                $lastStep->getLine()->willReturn($expectedLineOrStubIt)->shouldBeCalled();
            }
        }
    }

    /**
     * @param GherkinNodeTested|ObjectProphecy|null $eventOrStubIt
     *
     * @return string
     */
    protected function prophesizeResolveActionType(ObjectProphecy $eventOrStubIt = null)
    {
        return $eventOrStubIt
        && $eventOrStubIt instanceof AfterTested
            ? BehatStepLoggerSubscriber::HEADER_ACTION_OUT
            : BehatStepLoggerSubscriber::HEADER_ACTION_IN;
    }
}
