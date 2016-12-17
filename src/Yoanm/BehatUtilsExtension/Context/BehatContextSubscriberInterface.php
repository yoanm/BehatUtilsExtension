<?php
namespace Yoanm\BehatUtilsExtension\Context;

use Behat\Behat\Context\Context;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Want to listen Behat events (including those extension ones)
 * Just implement this interface your context will be passed to Behat dispatcher
 */
interface BehatContextSubscriberInterface extends Context, EventSubscriberInterface
{
}
