<?php
namespace Technical\Unit\Yoanm\BehatUtilsExtension\Logger\Processor;

use Monolog\Logger;
use Yoanm\BehatUtilsExtension\Logger\Processor\ClassHeaderProcessor;

class ClassHeaderProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ClassHeaderProcessor */
    private $processor;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->processor = new ClassHeaderProcessor();
    }

    public function testInvoke()
    {
        $message = 'my-message';
        $record['message'] = $message;

        $record = $this->processor->__invoke($record);

        $this->assertSame(
            sprintf(
                '[%s] %s',
                preg_replace('#(?:[^\\\]+\\\)#', '', self::class),
                $message
            ),
            $record['message']
        );
    }
}
