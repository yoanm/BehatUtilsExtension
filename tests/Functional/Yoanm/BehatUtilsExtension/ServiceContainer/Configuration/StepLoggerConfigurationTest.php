<?php
namespace Functional\Yoanm\BehatUtilsExtension\ServiceContainer\Configuration;

use Yoanm\BehatUtilsExtension\ServiceContainer\Configuration\StepLoggerConfiguration;

class StepLoggerConfigurationTest extends ConfigurationTestCase
{
    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return new StepLoggerConfiguration();
    }

    public function testDefaultConfiguration()
    {
        $config = $this->processConfiguration();

        $this->assertSame(false, $config['enabled']);
    }

    /**
     * @dataProvider provideEnableConfigValues
     * @param bool $enabled
     */
    public function testConfigurationCanBeEnabled($enabled)
    {
        $config = $this->processConfiguration([ $enabled ]);

        $this->assertSame($enabled, $config['enabled']);
    }

    /**
     * @return array
     */
    public function provideEnableConfigValues()
    {
        return [
            'Yes' => [ true ],
            'No' => [ false ],
        ];
    }
}
