<?php
namespace Yoanm\BehatUtilsExtension\Subscriber;

use Behat\Behat\EventDispatcher\Event\BackgroundTested;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\EventDispatcher\Event\GherkinNodeTested;
use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\StepContainerInterface;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class BehatStepLoggerSubscriber
 */
class BehatStepLoggerSubscriber implements EventSubscriberInterface
{
    const HEADER_ACTION_IN = 'IN';
    const HEADER_ACTION_OUT = 'OUT';

    const HEADER_NODE_FEATURE = 'FEATURE';
    const HEADER_NODE_BACKGROUND = 'BACKGROUND';
    const HEADER_NODE_SCENARIO = 'SCENARIO';
    const HEADER_NODE_OUTLINE = 'OUTLINE';
    const HEADER_NODE_EXAMPLE = 'EXAMPLE';
    const HEADER_NODE_STEP = 'STEP';

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        // Use high priority to log event at beginning
        $listener = ['logEvent', ListenerPriority::HIGH_PRIORITY];
        return [
            FeatureTested::BEFORE => $listener,
            BackgroundTested::BEFORE => $listener,
            ScenarioTested::BEFORE => $listener,
            OutlineTested::BEFORE => $listener,
            ExampleTested::BEFORE => $listener,
            StepTested::BEFORE => $listener,

            FeatureTested::AFTER => $listener,
            BackgroundTested::AFTER => $listener,
            ScenarioTested::AFTER => $listener,
            OutlineTested::AFTER => $listener,
            ExampleTested::AFTER => $listener,
            StepTested::AFTER => $listener,
        ];
    }

    /**
     * @param GherkinNodeTested $event
     */
    public function logEvent(GherkinNodeTested $event)
    {
        list($header, $context) = $this->processNodeEvent($event);

        $this->logger->debug($header, $context);
    }

    /**
     * @param GherkinNodeTested $event
     *
     * @return array
     */
    protected function processNodeEvent(GherkinNodeTested $event)
    {
        list($context, $nodeHeader) = $this->resolveContextAndNodeHeader($event);

        return [
            sprintf(
                '[%s][%s]',
                $nodeHeader,
                $this->resolveActionType($event)
            ),
            $context
        ];
    }

    /**
     * @param GherkinNodeTested $event
     *
     * @return string
     */
    protected function resolveActionType(GherkinNodeTested $event)
    {
        return $event instanceof AfterTested
            ? self::HEADER_ACTION_OUT
            : self::HEADER_ACTION_IN;
    }

    /**
     * @param GherkinNodeTested $event
     *
     * @return array
     */
    protected function resolveContextAndNodeHeader(GherkinNodeTested $event)
    {
        $context = [];
        switch (true) {
            case $event instanceof StepTested:
                $nodeHeader = self::HEADER_NODE_STEP;
                $context['text'] = $event->getStep()->getText();
                break;
            case $event instanceof BackgroundTested:
                $nodeHeader = self::HEADER_NODE_BACKGROUND;
                $context['title'] = $event->getBackground()->getTitle();
                break;
            case $event instanceof ScenarioTested:
                $scenario = $event->getScenario();
                $nodeHeader = self::HEADER_NODE_SCENARIO;
                if ($scenario instanceof ExampleNode) {
                    $nodeHeader = self::HEADER_NODE_EXAMPLE;
                    $context['tokens'] = $scenario->getTokens();
                }
                $context['title'] = $scenario->getTitle();
                break;
            case $event instanceof OutlineTested:
                $nodeHeader = self::HEADER_NODE_OUTLINE;
                $context['title'] = $event->getOutline()->getTitle();
                break;
            case $event instanceof FeatureTested:
                $nodeHeader = self::HEADER_NODE_FEATURE;
                $context['title'] = $event->getFeature()->getTitle();
                $context['file'] = $event->getFeature()->getFile();
                break;
            default:
                throw new \InvalidArgumentException(sprintf('"%s" not handled !', get_class($event)));
        }

        if (!$event instanceof FeatureTested) {
            $context['line'] = $this->resolveNodeLine($event);
        }

        return [
            $context,
            $nodeHeader
        ];
    }

    /**
     * @param GherkinNodeTested $event
     *
     * @return int
     */
    protected function resolveNodeLine(GherkinNodeTested $event)
    {
        $node = $event->getNode();
        $line = $node->getLine();

        if ($node instanceof StepContainerInterface
            && $event instanceof AfterTested
            && !$event instanceof StepTested /* no need to process step event end line */
        ) {
            // in case of end event, try to find the last line of the node

            /** @var StepContainerInterface $node*/
            $stepList = $node->getSteps();
            $lastStep = array_pop($stepList);

            // Check if StepContainer was not empty
            if ($lastStep instanceof StepNode) {
                $line = $lastStep->getLine();
            }
        }

        return $line;
    }
}
