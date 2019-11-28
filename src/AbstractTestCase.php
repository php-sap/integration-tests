<?php

namespace phpsap\IntegrationTests;

use phpsap\classes\Config\ConfigTypeA;
use phpsap\classes\Config\ConfigCommon;
use phpsap\interfaces\Config\IConfiguration;

/**
 * Class \phpsap\IntegrationTests\AbstractTestCase
 *
 * Helper class defining methods the connection and function tests will need.
 *
 * @package phpsap\IntegrationTests
 * @author  Gregor J.
 * @license MIT
 */
abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array A sample PHP/SAP configuration.
     */
    protected static $sampleSapConfig = [
        'ashost' => 'sap.example.com',
        'sysnr'  => '001',
        'client' => '002',
        'user'   => 'username',
        'passwd' => 'password'
    ];

    /**
     * AbstractTestCase constructor.
     * @param string|null  $name
     * @param array        $data
     * @param string       $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        SapRfcModuleMocks::requireFile($this->getModuleTemplateFile());
        SapRfcModuleMocks::validModuleFunctions($this->getValidModuleFunctions());
    }

    /**
     * Get a sample SAP config.
     * @return ConfigTypeA
     */
    protected function getSampleSapConfig()
    {
        return new ConfigTypeA(static::$sampleSapConfig);
    }

    /**
     * Load an actual sap configuration.
     * @return ConfigTypeA
     */
    protected function getSapConfig()
    {
        /**
         * Actual implementation has to return the path of a valid configuration file.
         */
        $configFile = $this->getSapConfigFile();
        if (file_exists($configFile) !== true) {
            throw new \RuntimeException(sprintf(
                'Cannot find config file %s!',
                $configFile
            ));
        }
        /**
         * Try to read the configuration file.
         */
        if (($configJson = file_get_contents($configFile)) === false) {
            throw new \RuntimeException(sprintf(
                'Cannot read from config file %s!',
                $configFile
            ));
        }
        /**
         * Let the Config* classes decide what to do with the given string.
         * In case the string is not JSON, an exception will be thrown.
         */
        return ConfigCommon::jsonDecode($configJson);
    }

    /**
     * Mock a SAP RFC module specific function or method.
     * @param string $name
     * @param \Closure $logic
     */
    public static function mock($name, $logic)
    {
        SapRfcModuleMocks::singleton()->mock($name, $logic);
    }

    /**
     * Get the name of the PHP module.
     * @return string
     */
    abstract public function getModuleName();

    /**
     * Get the path to the PHP/SAP configuration file.
     * @return string
     */
    abstract public function getSapConfigFile();

    /**
     * Get the path to the filename containing the SAP RFC module mockups.
     * @return string
     */
    abstract public function getModuleTemplateFile();

    /**
     * Get an array of valid SAP RFC module function or class method names.
     * @return array
     */
    abstract public function getValidModuleFunctions();

    /**
     * Create a new instance of a PHP/SAP connection class.
     * @param IConfiguration|null $config The PHP/SAP configuration. Default: null
     * @return \phpsap\interfaces\IConnection
     */
    abstract public function newConnection(IConfiguration $config = null);
}
