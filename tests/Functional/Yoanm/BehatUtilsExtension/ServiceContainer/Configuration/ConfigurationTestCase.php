<?php
namespace Functional\Yoanm\BehatUtilsExtension\ServiceContainer\Configuration;

use Symfony\Component\Config\Definition\Processor;
use Yoanm\BehatUtilsExtension\ServiceContainer\Configuration\ConfigurationInterface;

class ConfigurationTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @return ConfigurationInterface
     */
    public function getConfiguration()
    {
        throw new \Exception('You must override getConfiguration method to return your configuration class');
    }

    /**
     * @param array $configs
     * @return array
     */
    protected function processConfiguration(array $configs = [])
    {
        return (new Processor())->process(
            $this->getConfiguration()->getConfigNode()->getNode(true),
            $configs
        );
    }
}
