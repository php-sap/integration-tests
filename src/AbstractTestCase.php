<?php
/**
 * File src/AbstractTestCase.php
 *
 * Helper class defining methods the connection and function tests will need.
 *
 * @package integration-tests
 * @author  Gregor J.
 * @license MIT
 */
namespace phpsap\IntegrationTests;

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
        'ashost'    => 'sap.example.com',
        'sysnr'     => '001',
        'client'    => '002',
        'user'      => 'username',
        'passwd'    => 'password'
    ];

    /**
     * AbstractTestCase constructor.
     * @param null   $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        SapRfcModuleMocks::requireFile($this->getModuleTemplateFile());
        SapRfcModuleMocks::validModuleFunctions($this->getValidModuleFunctions());
    }

    /**
     * Get a sample SAP config.
     * @return array
     */
    protected function getSampleSapConfig()
    {
        return static::$sampleSapConfig;
    }

    /**
     * Load an actual sap configuration.
     * @return array
     */
    protected function getSapConfig()
    {
        $configFile = $this->getSapConfigFile();
        if (file_exists($configFile) !== true) {
            throw new \RuntimeException(sprintf(
                'Cannot find config file %s!',
                $configFile
            ));
        }

        if (($configJson = file_get_contents($configFile)) === false) {
            throw new \RuntimeException(sprintf(
                'Cannot read from config file %s!',
                $configFile
            ));
        }

        if (($configArr = json_decode($configJson, true)) === null) {
            throw new \RuntimeException(sprintf(
                'Invalid JSON format in config file %s!',
                $configFile
            ));
        }
        return $configArr;
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
     * @param array|string|null $config The PHP/SAP configuration. Default: null
     * @return \phpsap\interfaces\IConnection
     */
    abstract public function newConnection($config = null);
}
