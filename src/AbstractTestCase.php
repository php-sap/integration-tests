<?php

namespace phpsap\IntegrationTests;

use phpsap\classes\Api\RemoteApi;
use phpsap\classes\Config\ConfigTypeA;
use phpsap\classes\Config\ConfigCommon;
use phpsap\classes\Config\ConfigTypeB;
use phpsap\interfaces\Api\IApi;
use phpsap\interfaces\Config\IConfiguration;
use phpsap\interfaces\IFunction;

/**
 * Class \phpsap\IntegrationTests\AbstractTestCase
 *
 * Helper class defining methods the implementation specific tests will need.
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
        SapRfcModuleMocks::requireFile(static::getModuleTemplateFile());
        SapRfcModuleMocks::validModuleFunctions(static::getValidModuleFunctions());
    }

    /**
     * Get a sample SAP config.
     * @return ConfigTypeA
     */
    public static function getSampleSapConfig()
    {
        return new ConfigTypeA(static::$sampleSapConfig);
    }

    /**
     * Load an actual sap configuration.
     * @return ConfigTypeA|ConfigTypeB
     */
    public static function getActualSapConfig()
    {
        /**
         * Actual implementation has to return the path of a valid configuration file.
         */
        $configFile = static::getSapConfigFile();
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
     * Load an actual SAP remote function call API description from file.
     * @param string $remoteFunction
     * @return \phpsap\classes\Api\RemoteApi
     */
    public static function getApi($remoteFunction)
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR;
        $file .= sprintf('%s.json', $remoteFunction);
        if (file_exists($file) !== true) {
            throw new \RuntimeException(sprintf(
                'Cannot find SAP remote function API file %s!',
                $file
            ));
        }
        /**
         * Try to read the configuration file.
         */
        if (($json = file_get_contents($file)) === false) {
            throw new \RuntimeException(sprintf(
                'Cannot read from SAP remote function API file %s!',
                $file
            ));
        }
        return RemoteApi::jsonDecode($json);
    }

    /**
     * Initialize the remote function call with at least a name.
     * In order to add SAP remote function call parameters, an API needs to be
     * defined. In case no SAP remote function call API has been defined, it will be
     * queried on the fly by connecting to the SAP remote system. In order to
     * connect to the SAP remote system, you need a connection configuration.
     * @param string                                        $name   SAP remote function name.
     * @param array|null                                    $params SAP remote function call parameters. Default: null
     * @param \phpsap\interfaces\Config\IConfiguration|null $config Connection configuration. Default: null
     * @param \phpsap\interfaces\Api\IApi|null              $api    SAP remote function call API. Default: null
     * @return IFunction
     * @throws \phpsap\interfaces\exceptions\IInvalidArgumentException
     */
    public static function newSapRfc($name, array $params = null, IConfiguration $config = null, IApi $api = null)
    {
        $class = static::getClassName();
        return new $class($name, $params, $config, $api);
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
     * Return the name of the class, used for testing.
     * @return string
     */
    abstract public static function getClassName();

    /**
     * Get the name of the PHP module.
     * @return string
     */
    abstract public static function getModuleName();

    /**
     * Get the path to the PHP/SAP configuration file.
     * @return string
     */
    abstract public static function getSapConfigFile();

    /**
     * Get the path to the filename containing the SAP RFC module mockups.
     * @return string
     */
    abstract public static function getModuleTemplateFile();

    /**
     * Get an array of valid SAP RFC module function or class method names.
     * @return array
     */
    abstract public static function getValidModuleFunctions();
}
