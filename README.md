# BehatUtilsExtension
[![Scrutinizer Build Status](https://img.shields.io/scrutinizer/build/g/yoanm/BehatUtilsExtension.svg?label=Scrutinizer)](https://scrutinizer-ci.com/g/yoanm/BehatUtilsExtension/build-status/master) [![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/yoanm/BehatUtilsExtension.svg?label=Code%20quality)](https://scrutinizer-ci.com/g/yoanm/BehatUtilsExtension/?branch=master) [![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/yoanm/BehatUtilsExtension.svg?label=Coverage)](https://scrutinizer-ci.com/g/yoanm/BehatUtilsExtension/?branch=master)

[![Travis Build Status](https://img.shields.io/travis/yoanm/BehatUtilsExtension/master.svg?label=travis)](https://travis-ci.org/yoanm/BehatUtilsExtension) [![PHP Versions](https://img.shields.io/badge/php-5.5%20%2F%205.6%20%2F%207.0-8892BF.svg)](https://php.net/)

[![Latest Stable Version](https://img.shields.io/packagist/v/yoanm/behat-utils-extension.svg)](https://packagist.org/packages/yoanm/behat-utils-extension)

BehatUtilsExtension provide a set of utility for Behat 3.0+

* [How to use](#how-to-use)
   * [Installation](#installation)
   * [Configuration](#configuration)
* [In the box](#in-the-box)
   * [Logger](#logger)
   * [Behat event subscription](#behat-event-subscription)
   * [Step logger](#step-logger)
* [Default configuration reference](#default-configuration-reference)
* [Tests](#tests)


## How to use
### Installation
```bash
> composer require --dev yoanm/behat-utils-extension
```

BehatUtilsExtension require [behat/behat](https://github.com/Behat/Behat) and [monolog/monolog](https://github.com/Seldaek/monolog)

### Configuration
Add the following in your behat configuration file (usually `behat.yml`) :
```yaml
default:
    extensions:
        Yoanm\BehatUtilsExtension: ~
```

## In the box

### Logger
See [`LoggerAwareInterface`](src/Yoanm/BehatUtilsExtension/Context/LoggerAwareInterface.php)

Implements this interface and your context will have a logger injected

#### Example
```php
<?php
namespace Functional\Yoanm\BehatUtilsExtension\Context;

use Behat\Behat\Context\Context;
use Psr\Log\LoggerInterface;
use Yoanm\BehatUtilsExtension\Context\LoggerAwareInterface;

class FeatureContext implements Context, LoggerAwareInterface
{
    /** @var LoggerInterface */
    private $logger;

    /**
     * {@inheritdoc}
     */
    public function setBehatLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @When my step
     */
    public function myStep()
    {
        $this->logger->info('Executing my step');
    }
}
```

### Behat event subscription
See [`BehatContextSubscriberInterface`](src/Yoanm/BehatUtilsExtension/Context/BehatContextSubscriberInterface.php)

Implements this interface and your context will be passed to Behat dispatcher in order to receive behat events

#### Example
```php
<?php
namespace Functional\Yoanm\BehatUtilsExtension\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\GherkinNodeTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Yoanm\BehatUtilsExtension\Context\BehatContextSubscriberInterface;

class FeatureContext implements Context, BehatContextSubscriberInterface
{
    /**
     * @param GherkinNodeTested $event
     */
    public function reset(GherkinNodeTested $event)
    {
        /**
         * Reset here your data before a scenario or example is started
         */
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ScenarioTested::BEFORE => ['reset'],
            ExampleTested::BEFORE => ['reset'],
            //Or
            // ScenarioTested::BEFORE => ['reset', ListenerPriority::HIGH_PRIORITY],
        ];
    }
}
```

### Step logger
**Only in case where `step_logger.enabled` is set to true** (see [default configuration reference](#default-configuration-reference) below).

See [`BehatStepLoggerSubscriber`](src/Yoanm/BehatUtilsExtension/Subscriber/BehatStepLoggerSubscriber.php)

If enabled, will log each behat feature/background/outline/example/step start and end. Usefull to understand what happens behing the scene if you have a non understandable error in you features.

Could be use with [Logger](#logger) to easily spot an issue.

#### Output example
The following will be appended in the configured log file (see [default configuration reference](#default-configuration-reference) below).
```
behatUtils.DEBUG: [BehatStepLoggerSubscriber] [FEATURE][IN] {"title":"FEATURE TITLE","file":"FEATURE FILE PATH"} []
behatUtils.DEBUG: [BehatStepLoggerSubscriber] [FEATURE][OUT] {"title":"FEATURE TITLE","file":"FEATURE FILE PATH"} []

behatUtils.DEBUG: [BehatStepLoggerSubscriber] [BACKGROUND][IN] {"title":"BACKGROUND TITLE","line":"BACKGROUND START LINE"} []
behatUtils.DEBUG: [BehatStepLoggerSubscriber] [BACKGROUND][OUT] {"title":"BACKGROUND TITLE","line":"BACKGROUND END LINE"} []

behatUtils.DEBUG: [BehatStepLoggerSubscriber] [OUTLINE][IN] {"title":"OUTLINE TITLE","line": "OUTLINE START LINE"} []
behatUtils.DEBUG: [BehatStepLoggerSubscriber] [OUTLINE][OUT] {"title":"OUTLINE TITLE","line": "OUTLINE END LINE"} []

behatUtils.DEBUG: [BehatStepLoggerSubscriber] [EXAMPLE][IN] {"tokens":{"EXAMPLE_TOKENS_NAME":"EXAMPLE_TOKENS_VALUE"},"title":"| EXAMPLE_TOKENS_VALUE|","line":"EXAMPLE START LINE"} []
behatUtils.DEBUG: [BehatStepLoggerSubscriber] [EXAMPLE][OUT] {"tokens":{"EXAMPLE_TOKENS_NAME":"EXAMPLE_TOKENS_VALUE"},"title":"| EXAMPLE_TOKENS_VALUE|","line":"EXAMPLE END LINE"} []

behatUtils.DEBUG: [BehatStepLoggerSubscriber] [STEP][IN] {"text":"STEP TEXT","line":"STEP LINE"} []
behatUtils.DEBUG: [BehatStepLoggerSubscriber] [STEP][OUT] {"text":"STEP TEXT","line":"STEP LINE"} []
```

## Default configuration reference
```yaml
default:
    extensions:
        Yoanm\BehatUtilsExtension:
            logger:
                path: behat.log
                level: INFO
            step_logger:
                enabled: false
```

## Contributing
See [contributing note](./CONTRIBUTING.md)
