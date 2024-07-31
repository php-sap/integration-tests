<?php

declare(strict_types=1);

namespace phpsap\IntegrationTests;

use Closure;
use LogicException;
use phpsap\interfaces\exceptions\IInvalidArgumentException;
use PHPUnit\Framework\TestCase;
use phpsap\classes\Api\RemoteApi;
use phpsap\classes\Config\ConfigTypeA;
use phpsap\interfaces\Api\IApi;
use phpsap\interfaces\Config\IConfiguration;
use phpsap\interfaces\IFunction;
use RuntimeException;

/**
 * Class \phpsap\IntegrationTests\AbstractTestCase
 *
 * Helper class defining methods the implementation specific tests will need.
 *
 * @package phpsap\IntegrationTests
 * @author  Gregor J.
 * @license MIT
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @var array A sample PHP/SAP configuration.
     */
    protected static array $sampleSapConfig = [
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
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        SapRfcModuleMocks::requireFile(static::getModuleTemplateFile());
        SapRfcModuleMocks::validModuleFunctions(static::getValidModuleFunctions());
    }

    /**
     * Get a sample SAP config.
     * @return ConfigTypeA
     */
    public static function getSampleSapConfig(): ConfigTypeA
    {
        return new ConfigTypeA(static::$sampleSapConfig);
    }

    /**
     * Load an actual sap configuration.
     * @return IConfiguration
     */
    public static function getActualSapConfig(): IConfiguration
    {
        /**
         * Actual implementation has to return the path of a valid configuration file.
         */
        $configFile = static::getSapConfigFile();
        if (file_exists($configFile) !== true) {
            throw new RuntimeException(sprintf(
                'Cannot find config file %s!',
                $configFile
            ));
        }
        /**
         * Try to read the configuration file.
         */
        if (($configJson = file_get_contents($configFile)) === false) {
            throw new RuntimeException(sprintf(
                'Cannot read from config file %s!',
                $configFile
            ));
        }
        /**
         * Let the Config* classes decide what to do with the given string.
         * In case the string is not JSON, an exception will be thrown.
         */
        return ConfigTypeA::jsonDecode($configJson);
    }

    /**
     * Load an actual SAP remote function call API description from file.
     * @param string $remoteFunction
     * @return RemoteApi
     */
    public static function getApi(string $remoteFunction): RemoteApi
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR;
        $file .= sprintf('%s.json', $remoteFunction);
        if (file_exists($file) !== true) {
            throw new RuntimeException(sprintf(
                'Cannot find SAP remote function API file %s!',
                $file
            ));
        }
        /**
         * Try to read the configuration file.
         */
        if (($json = file_get_contents($file)) === false) {
            throw new RuntimeException(sprintf(
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
     * @param  string  $name   SAP remote function name.
     * @param array|null           $params SAP remote function call parameters. Default: null
     * @param  IConfiguration|null $config Connection configuration. Default: null
     * @param  IApi|null           $api    SAP remote function call API. Default: null
     * @return IFunction
     * @throws IInvalidArgumentException
     */
    public static function newSapRfc(string $name, array $params = null, IConfiguration $config = null, IApi $api = null): IFunction
    {
        $class = static::getClassName();
        return new $class($name, $params, $config, $api);
    }

    /**
     * Mock a SAP RFC module specific function or method.
     * @param  string  $name
     * @param  Closure  $logic
     */
    public static function mock(string $name, Closure $logic): void
    {
        SapRfcModuleMocks::singleton()->mock($name, $logic);
    }

    /**
     * Return the name of the class, used for testing.
     * @return string
     */
    public static function getClassName(): string
    {
        throw new LogicException(sprintf('Unimplemented %s::%s()', static::class, __FUNCTION__));
    }

    /**
     * Get the name of the PHP module.
     * @return string
     */
    abstract public static function getModuleName(): string;

    /**
     * Get the path to the PHP/SAP configuration file.
     * @return string
     */
    public static function getSapConfigFile(): string
    {
        throw new LogicException(sprintf('Unimplemented %s::%s()', static::class, __FUNCTION__));
    }

    /**
     * Get the path to the filename containing the SAP RFC module mockups.
     * @return string
     */
    abstract public static function getModuleTemplateFile(): string;

    /**
     * Get an array of valid SAP RFC module function or class method names.
     * @return array
     */
    abstract public static function getValidModuleFunctions(): array;
}
