<?php
namespace Yoanm\BehatUtilsExtension\Context;

use Behat\Behat\Context\Context;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Want to listen Behat events
 * Just implement this interface and your context will be passed to Behat dispatcher
 */
interface BehatContextSubscriberInterface extends Context, EventSubscriberInterface
{
}
