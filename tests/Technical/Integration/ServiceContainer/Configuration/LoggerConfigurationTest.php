<?php
namespace Technical\Integration\Yoanm\BehatUtilsExtension\ServiceContainer\Configuration;

use Monolog\Logger;
use Yoanm\BehatUtilsExtension\ServiceContainer\Configuration\LoggerConfiguration;

class LoggerConfigurationTest extends ConfigurationTestCase
{
    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return new LoggerConfiguration();
    }


    public function testDefaultConfiguration()
    {
        $config = $this->processConfiguration();

        // Assert default path
        $this->assertSame('behat.log', $config['path']);
        // Assert default log level
        $this->assertSame(Logger::INFO, $config['level']);
    }

    /**
     * @dataProvider provideLogLevelData
     *
     * @param int|string $logLevel
     * @param int|string $expectedLogLevel
     */
    public function testLogLevelIsNormalized($logLevel, $expectedLogLevel)
    {
        $config = $this->processConfiguration([ ['level' => $logLevel] ]);

        $this->assertSame($expectedLogLevel, $config['level']);
    }

    /**
     * @return array
     */
    public function provideLogLevelData()
    {
        return [
            'Basic' => [
                'logLevel' => 'DEBUG',
                'expectedLogLevel' => Logger::DEBUG
            ],
            'Basic lowercase' => [
                'logLevel' => 'debug',
                'expectedLogLevel' => Logger::DEBUG
            ],
            'Use integer' => [
                'logLevel' => 200,
                'expectedLogLevel' => 200
            ],
            'Invalid log level' => [
                'logLevel' => 'plop',
                'expectedLogLevel' => 'plop'
            ]
        ];
    }
}
