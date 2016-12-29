<?php
namespace FunctionalTest\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\EventDispatcher\Event\AfterBackgroundTested;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\BackgroundTested;
use Behat\Behat\EventDispatcher\Event\BeforeBackgroundTested;
use Behat\Behat\EventDispatcher\Event\BeforeOutlineTested;
use Behat\Behat\EventDispatcher\Event\BeforeScenarioTested;
use Behat\Behat\EventDispatcher\Event\BeforeStepTested;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\GherkinNodeTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Behat\Testwork\EventDispatcher\Event\BeforeTested;
use Yoanm\BehatUtilsExtension\Context\BehatContextSubscriberInterface;
use Yoanm\BehatUtilsExtension\Subscriber\BehatStepLoggerSubscriber;
use Yoanm\BehatUtilsExtension\Subscriber\ListenerPriority;

class BehatEventsContext implements Context, BehatContextSubscriberInterface
{
    const BEHAT_STEP_EVENT_LISTENER_SCENARIO_TAG = 'enable-behat-step-event-listener';

    /** @var GherkinNodeTested[] */
    private $behatStepEvents = [];
    /** @var bool */
    private $listenEvent = false;

    /** @var null|ScenarioNode|ExampleNode */
    private $currentScenario;
    /** @var null|BackgroundNode */
    private $currentBackground;
    /** @var null|StepNode */
    private $currentStep;

    /** @var bool */
    private $expectScenarioEndEvent = false;
    /** @var bool */
    private $expectExampleEndEvent = false;
    /** @var bool */
    private $expectBackgroundEndEvent = false;
    /** @var bool */
    private $expectStepEndEvent = false;

    /**
     * @Given I listen for behat steps event
     */
    public function iListenForBehatStepsEvent()
    {
        $this->listenEvent = true;
        $this->resetEventList();
    }

    /**
     * @Given /^I should have caught event regarding current (?P<type>(?:background|scenario|example)) start event$/
     */
    public function iShouldHaveCaughtEventRegardingNodeStart($type)
    {
        $eventData = $this->shiftEvent();
        $this->assertStartEventInstanceOf($eventData, $type);
        $this->assertEventArgs($eventData, $type, true);
    }

    /**
     * @Then /^I will catch event regarding current (?P<type>(?:background|scenario|example)) end event$/
     */
    public function iWillCaughtEventRegardingNodeEnd($type)
    {
        switch (strtoupper($type)) {
            case BehatStepLoggerSubscriber::HEADER_NODE_BACKGROUND:
                $this->expectBackgroundEndEvent = true;
                break;
            case BehatStepLoggerSubscriber::HEADER_NODE_SCENARIO:
                $this->expectScenarioEndEvent = true;
                break;
            case BehatStepLoggerSubscriber::HEADER_NODE_EXAMPLE:
                $this->expectExampleEndEvent = true;
                break;
            default:
                throw new \InvalidArgumentException(sprintf('"%s" not handled !', $type));
        }
    }

    /**
     * @Given I should have caught event regarding current step start event and will have the end event
     */
    public function iShouldHaveCaughtEventRegardingCurrentStepStartAndEnd()
    {
        // Shift an event that is the AfterTested event from previous node
        $this->shiftEvent();
        /** @var BeforeStepTested $event */
        $eventData = $this->shiftEvent();
        $this->assertStartEventInstanceOf($eventData, 'step');
        $this->assertEventArgs($eventData, 'step', true);
        $this->expectStepEndEvent = true;
    }

    /**
     * @param BeforeScenarioTested|BeforeOutlineTested $event
     */
    public function setUp(BeforeTested $event)
    {
        if (in_array(
            self::BEHAT_STEP_EVENT_LISTENER_SCENARIO_TAG,
            array_merge(
                $event->getScenario()->getTags(),
                $event->getFeature()->getTags()
            )
        )) {
            //Auto listen
            $this->iListenForBehatStepsEvent();
        } else {
            $this->resetEventList();
        }
    }

    /**
     * @param GherkinNodeTested $event
     */
    public function storeEvent(GherkinNodeTested $event, $name)
    {
        if (true === $this->listenEvent) {
            $this->behatStepEvents[] = [$event, $name];
        }
    }

    /**
     * @param GherkinNodeTested $event
     */
    public function setNodeContext(GherkinNodeTested $event)
    {
        if ($this->listenEvent) {
            $isAfter = $event instanceof AfterTested;
            switch (true) {
                case $event instanceof ScenarioTested:
                case $event instanceof ExampleTested:
                    $this->currentScenario = $isAfter ? null : $event->getScenario();
                    break;
                case $event instanceof BackgroundTested:
                    $this->currentBackground = $isAfter ? null : $event->getBackground();
                    break;
                case $event instanceof StepTested:
                    $this->currentStep = $isAfter ? null : $event->getStep();
                    break;
            }
        }
    }

    /**
     * @param GherkinNodeTested $event
     * @param string            $name
     */
    public function checkEndEventExpectation(GherkinNodeTested $event, $name)
    {
        if ($this->listenEvent) {
            /* if event received, disable expectation */
            if ($event instanceof StepTested) {
                if (true === $this->expectStepEndEvent) {
                    $this->assertEventArgs([$event, $name], 'step', false);
                }
                $this->expectStepEndEvent = false;
            } elseif ($event instanceof BackgroundTested) {
                if (true === $this->expectBackgroundEndEvent) {
                    $this->assertEventArgs([$event, $name], 'background', false);
                }
                $this->expectBackgroundEndEvent = false;
            } else {
                if (ExampleTested::AFTER === $name) {
                    if (true === $this->expectExampleEndEvent) {
                        $this->assertEventArgs([$event, $name], 'example', false);
                    }
                    $this->expectExampleEndEvent = false;
                } else {
                    if (true === $this->expectScenarioEndEvent) {
                        $this->assertEventArgs([$event, $name], 'scenario', false);
                    }
                    $this->expectScenarioEndEvent = false;
                }
            }
        }
    }

    /**
     * @param GherkinNodeTested $event
     */
    public function tearDown(GherkinNodeTested $event)
    {
        if ($this->listenEvent) {
            // Check that all required expectations have been validated
            if ($event instanceof AfterScenarioTested) {
                \PHPUnit_Framework_Assert::assertFalse(
                    $this->expectScenarioEndEvent,
                    'Scenario end event expected but not catched !'
                );
                \PHPUnit_Framework_Assert::assertFalse(
                    $this->expectExampleEndEvent,
                    'Example end event expected but not catched !'
                );
            }

            if (
                $event instanceof AfterBackgroundTested
                || $event instanceof AfterScenarioTested
            ) {
                \PHPUnit_Framework_Assert::assertFalse(
                    $this->expectBackgroundEndEvent,
                    'Background end event expected but not catched !'
                );
            }

            // Following must be always true
            \PHPUnit_Framework_Assert::assertFalse(
                $this->expectStepEndEvent,
                'Step end event expected but not catched !'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $storeEventListener = ['storeEvent', ListenerPriority::HIGH_PRIORITY];

        $beforeStepListenerList = [
            ['setNodeContext', ListenerPriority::HIGH_PRIORITY],
            $storeEventListener,
        ];
        $beforeScenarioListenerList = array_merge(
            [ ['setUp', ListenerPriority::HIGH_PRIORITY] ],
            $beforeStepListenerList
        );
        $afterListenerList = [
            $storeEventListener,
            ['checkEndEventExpectation', ListenerPriority::HIGH_PRIORITY],
            ['tearDown', ListenerPriority::HIGH_PRIORITY],
            ['setNodeContext', ListenerPriority::LOW_PRIORITY],
        ];
        return [
            ScenarioTested::BEFORE => $beforeScenarioListenerList,
            ExampleTested::BEFORE => $beforeScenarioListenerList,

            BackgroundTested::BEFORE => $beforeStepListenerList,
            StepTested::BEFORE => $beforeStepListenerList,

            StepTested::AFTER => $afterListenerList,
            BackgroundTested::AFTER => $afterListenerList,

            ScenarioTested::AFTER => $afterListenerList,
            ExampleTested::AFTER => $afterListenerList,
        ];
    }

    /**
     * @return array|null Event as first value and event name as second value
     */
    protected function shiftEvent()
    {
        return array_shift($this->behatStepEvents);
    }

    protected function resetEventList()
    {
        $this->behatStepEvents = [];
    }

    protected function assertStartEventInstanceOf(array $eventData, $type)
    {
        switch (strtoupper($type)) {
            case BehatStepLoggerSubscriber::HEADER_NODE_BACKGROUND:
                $className = BeforeBackgroundTested::class;
                break;
            case BehatStepLoggerSubscriber::HEADER_NODE_EXAMPLE:
            case BehatStepLoggerSubscriber::HEADER_NODE_SCENARIO:
                $className = BeforeScenarioTested::class;
                break;
            case BehatStepLoggerSubscriber::HEADER_NODE_STEP:
                $className = BeforeStepTested::class;
                break;
            default:
                throw new \InvalidArgumentException(sprintf('"%s" not handled !', $type));
        }
        \PHPUnit_Framework_Assert::assertInstanceOf(
            $className,
            $eventData[0],
            sprintf('Failed asserting that start %s event has been received !', $type)
        );
    }

    protected function assertEventArgs(array $eventData, $type, $isStart)
    {
        switch (strtoupper($type)) {
            case BehatStepLoggerSubscriber::HEADER_NODE_BACKGROUND:
                $expected = $this->currentBackground->getTitle();
                $current = $eventData[0]->getNode()->getTitle();
                break;
            case BehatStepLoggerSubscriber::HEADER_NODE_SCENARIO:
                $expected = $this->currentScenario->getTitle();
                $current = $eventData[0]->getNode()->getTitle();
                break;
            case BehatStepLoggerSubscriber::HEADER_NODE_EXAMPLE:
                $expected = $this->currentScenario->getTokens();
                $current = $eventData[0]->getNode()->getTokens();
                break;
            case BehatStepLoggerSubscriber::HEADER_NODE_STEP:
                $expected = $this->currentStep->getText();
                $current = $eventData[0]->getNode()->getText();
                break;
            default:
                throw new \InvalidArgumentException(sprintf('"%s" not handled !', $type));
        }

        \PHPUnit_Framework_Assert::assertSame(
            $expected,
            $current,
            sprintf(
                'Failed asserting that %s %s event is the right one !',
                $isStart ? 'start' : 'end',
                $type
            )
        );

    }
}
