<?php
namespace Yoanm\BehatUtilsExtension\Context;

use Behat\Behat\Context\Context;
use Psr\Log\LoggerInterface;

/**
 * Want to log something in a context, for debug purpose for instance ?
 * Just implement this interface and the BehatUtilsExtension logger will be injected
 */
interface LoggerAwareInterface extends Context
{
    /**
     * @param LoggerInterface $logger
     */
    public function setBehatLogger(LoggerInterface $logger);
}
