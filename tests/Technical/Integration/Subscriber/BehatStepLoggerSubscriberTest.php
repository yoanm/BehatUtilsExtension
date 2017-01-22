<?php
namespace Technical\Integration\Yoanm\BehatUtilsExtension\Subscriber;

use Behat\Behat\Definition\SearchResult;
use Behat\Behat\EventDispatcher\Event\AfterBackgroundTested;
use Behat\Behat\EventDispatcher\Event\AfterFeatureTested;
use Behat\Behat\EventDispatcher\Event\AfterOutlineTested;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\BackgroundTested;
use Behat\Behat\EventDispatcher\Event\BeforeBackgroundTested;
use Behat\Behat\EventDispatcher\Event\BeforeFeatureTested;
use Behat\Behat\EventDispatcher\Event\BeforeOutlineTested;
use Behat\Behat\EventDispatcher\Event\BeforeScenarioTested;
use Behat\Behat\EventDispatcher\Event\BeforeStepTested;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\EventDispatcher\Event\GherkinNodeTested;
use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\ExampleTableNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Call\Call;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\Environment\StaticEnvironment;
use Behat\Testwork\Suite\GenericSuite;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\Setup\SuccessfulTeardown;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
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
     * @dataProvider provideExpectedLogMessage
     *
     * @param GherkinNodeTested $event
     * @param string $expectedHeader
     * @param array $expectedContext
     */
    public function testLogMessage(GherkinNodeTested $event, $expectedHeader, array $expectedContext)
    {
        $this->logger->debug($expectedHeader, $expectedContext)->shouldBeCalled();

        $this->subscriber->logEvent($event);
    }

    public function testWithUnexpectedEvent()
    {
        $event = $this->prophesize(GherkinNodeTested::class)->reveal();
        $this->setExpectedException(
            \InvalidArgumentException::class,
            sprintf('"%s" not handled !', get_class($event))
        );
        $this->subscriber->logEvent($event);
    }

    /**
     * @return array
     */
    public function provideExpectedLogMessage()
    {
        $env = new StaticEnvironment(new GenericSuite('suite', []));
        $firstStep = new StepNode(
            'first step keyword',
            'first step text',
            [],
            'first step line',
            'first step keyword type'
        );
        $lastStep = new StepNode(
            'last step keyword',
            'last step text',
            [],
            'last step line',
            'last step keyword type'
        );
        $scenario = new ScenarioNode(
            'scenario title',
            [],
            [$firstStep, $lastStep],
            'scenario keyword',
            'scenario line'
        );
        $feature = new FeatureNode(
            'feature title',
            'feature description',
            [],
            null,
            [$scenario],
            'feature keyword',
            'feature language',
            'feature file',
            'feature line'
        );

        $example = new ExampleNode('example title', [], [$firstStep, $lastStep], ['tokens'], 'example line');
        $exampleFeature = new FeatureNode(
            'feature title',
            'feature description',
            [],
            null,
            [$example],
            'feature keyword',
            'feature language',
            'feature file',
            'feature line'
        );

        $background = new BackgroundNode(
            'background title',
            [$firstStep, $lastStep],
            'background keyword',
            'background line'
        );
        $backgroundFeature = new FeatureNode(
            'feature title',
            'feature description',
            [],
            $background,
            [$scenario],
            'feature keyword',
            'feature language',
            'feature file',
            'feature line'
        );

        $outline = new OutlineNode(
            'outline title',
            [],
            [$firstStep, $lastStep],
            new ExampleTableNode([], 'example table node keyword'),
            'outline keyword',
            'outline line'
        );

        $outlineFeature = new FeatureNode(
            'feature title',
            'feature description',
            [],
            null,
            [$outline],
            'feature keyword',
            'feature language',
            'feature file',
            'feature line'
        );

        $testResult = new TestResults();
        $stepTestResult = new ExecutedStepResult(
            new SearchResult(),
            new CallResult($this->prophesize(Call::class)->reveal(), 'plop')
        );
        $tearDown = new SuccessfulTeardown();

        return [

            // Before
            'FeatureTested::BEFORE' => [
                'event' => new BeforeFeatureTested($env, $feature),
                'expectedHeader' => '[FEATURE][IN]',
                'expectedContext' => ['title' => 'feature title', 'file' => 'feature file'],
            ],
            'BackgroundTested::BEFORE' => [
                'event' => new BeforeBackgroundTested($env, $backgroundFeature, $backgroundFeature->getBackground()),
                'expectedHeader' => '[BACKGROUND][IN]',
                'expectedContext' => ['title' => 'background title', 'line' => 'background line'],
            ],
            'ScenarioTested::BEFORE' => [
                'event' => new BeforeScenarioTested($env, $feature, $feature->getScenarios()[0]),
                'expectedHeader' => '[SCENARIO][IN]',
                'expectedContext' => ['title' => 'scenario title', 'line' => 'scenario line'],
            ],
            'OutlineTested::BEFORE' => [
                'event' => new BeforeOutlineTested($env, $outlineFeature, $outlineFeature->getScenarios()[0]),
                'expectedHeader' => '[OUTLINE][IN]',
                'expectedContext' => ['title' => 'outline title', 'line' => 'outline line'],
            ],
            'ExampleTested::BEFORE' => [
                'event' => new BeforeScenarioTested($env, $exampleFeature, $exampleFeature->getScenarios()[0]),
                'expectedHeader' => '[EXAMPLE][IN]',
                'expectedContext' => [
                    'tokens' => ['tokens'],
                    'title' => 'example title',
                    'line' => 'example line'
                ],
            ],
            'StepTested::BEFORE' => [
                'event' => new BeforeStepTested($env, $feature, $feature->getScenarios()[0]->getSteps()[0]),
                'expectedHeader' => '[STEP][IN]',
                'expectedContext' => ['text' => 'first step text', 'line' => 'first step line'],
            ],

            // After
            'FeatureTested::AFTER' => [
                'event' => new AfterFeatureTested($env, $feature, $testResult, $tearDown),
                'expectedHeader' => '[FEATURE][OUT]',
                'expectedContext' => ['title' => 'feature title', 'file' => 'feature file'],
            ],
            'BackgroundTested::AFTER' => [
                'event' => new AfterBackgroundTested(
                    $env,
                    $backgroundFeature,
                    $backgroundFeature->getBackground(),
                    $testResult,
                    $tearDown
                ),
                'expectedHeader' => '[BACKGROUND][OUT]',
                'expectedContext' => ['title' => 'background title', 'line' => 'last step line'],
            ],
            'ScenarioTested::AFTER' => [
                'event' => new AfterScenarioTested(
                    $env,
                    $feature,
                    $feature->getScenarios()[0],
                    $testResult,
                    $tearDown
                ),
                'expectedHeader' => '[SCENARIO][OUT]',
                'expectedContext' => ['title' => 'scenario title', 'line' => 'last step line'],
            ],
            'OutlineTested::AFTER' => [
                'event' => new AfterOutlineTested(
                    $env,
                    $outlineFeature,
                    $outlineFeature->getScenarios()[0],
                    $testResult,
                    $tearDown
                ),
                'expectedHeader' => '[OUTLINE][OUT]',
                'expectedContext' => ['title' => 'outline title', 'line' => 'last step line'],
            ],
            'ExampleTested::AFTER' => [
                'event' => new AfterScenarioTested(
                    $env,
                    $exampleFeature,
                    $exampleFeature->getScenarios()[0],
                    $testResult,
                    $tearDown
                ),
                'expectedHeader' => '[EXAMPLE][OUT]',
                'expectedContext' => [
                    'tokens' => ['tokens'],
                    'title' => 'example title',
                    'line' => 'last step line'
                ],
            ],
            'StepTested::AFTER' => [
                'event' => new AfterStepTested(
                    $env,
                    $feature,
                    $exampleFeature->getScenarios()[0]->getSteps()[1],
                    $stepTestResult,
                    $tearDown
                ),
                'expectedHeader' => '[STEP][OUT]',
                'expectedContext' => ['text' => 'last step text', 'line' => 'last step line'],
            ],
        ];
    }
}
