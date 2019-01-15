<?php
/**
 * File src/AbstractConfigBTestCase.php
 *
 * Test config type B.
 *
 * @package integration-tests
 * @author  Gregor J.
 * @license MIT
 */

namespace phpsap\IntegrationTests;

use phpsap\classes\AbstractConfigB;
use phpsap\exceptions\IncompleteConfigException;
use phpsap\interfaces\IConfig;
use phpsap\interfaces\IConfigB;
use PHPUnit\Framework\TestCase;

/**
 * Class \phpsap\IntegrationTests\AbstractConfigBTestCase
 *
 * Test config type B.
 *
 * @package phpsap\IntegrationTests
 * @author  Gregor J.
 * @license MIT
 */
abstract class AbstractConfigBTestCase extends TestCase
{
    /**
     * Test config type B inheritance chain.
     */
    public function testInheritance()
    {
        $config = $this->newConfigB();
        static::assertInstanceOf(IConfig::class, $config);
        static::assertInstanceOf(IConfigB::class, $config);
        static::assertInstanceOf(AbstractConfigB::class, $config);
    }

    /**
     * Test a valid config creation.
     */
    public function testValidConfig()
    {
        $configArr = [
            'client' => '02',
            'user' => 'username',
            'passwd' => 'password',
            'mshost' => 'sap.example.com',
            'r3name' => 'system_id',
            'group' => 'logon_group',
            'lang' => 'EN',
            'trace' => IConfigB::TRACE_VERBOSE
        ];
        $configJson = json_encode($configArr);
        $config = $this->newConfigB($configJson);
        $configSaprfc = $config->generateConfig();
        static::assertInternalType('array', $configSaprfc);
        static::assertArrayHasKey('CLIENT', $configSaprfc);
        static::assertSame('02', $configSaprfc['CLIENT']);
        static::assertArrayHasKey('USER', $configSaprfc);
        static::assertSame('username', $configSaprfc['USER']);
        static::assertArrayHasKey('PASSWD', $configSaprfc);
        static::assertSame('password', $configSaprfc['PASSWD']);
        static::assertArrayHasKey('MSHOST', $configSaprfc);
        static::assertSame('sap.example.com', $configSaprfc['MSHOST']);
        static::assertArrayHasKey('R3NAME', $configSaprfc);
        static::assertSame('system_id', $configSaprfc['R3NAME']);
        static::assertArrayHasKey('GROUP', $configSaprfc);
        static::assertSame('logon_group', $configSaprfc['GROUP']);
        static::assertArrayHasKey('LANG', $configSaprfc);
        static::assertSame('EN', $configSaprfc['LANG']);
        static::assertArrayHasKey('TRACE', $configSaprfc);
        static::assertSame(IConfigB::TRACE_VERBOSE, $configSaprfc['TRACE']);
    }

    /**
     * Data provider for incomplete config.
     * @return array
     */
    public static function incompleteConfig()
    {
        return [
            [
                [
                    'client' => '02',
                    'user' => 'username',
                    'passwd' => 'password',
                    'r3name' => 'system_id',
                    'group' => 'logon_group'
                ],
                'mshost'
            ],
            [
                [
                    'client' => '02',
                    'user' => 'username',
                    'passwd' => 'password',
                    'mshost' => 'sap.example.com',
                    'group' => 'logon_group',
                    'lang' => 'EN',
                    'trace' => IConfigB::TRACE_OFF
                ],
                'r3name'
            ]
        ];
    }

    /**
     * Test incomplete config exception.
     * @param array $configArr
     * @param string $missing
     * @dataProvider incompleteConfig
     */
    public function testIncompleteConfig($configArr, $missing)
    {
        $configJson = json_encode($configArr);
        $config = $this->newConfigB($configJson);
        $expectedMsg = sprintf('Missing mandatory key %s.', $missing);
        $this->expectException(IncompleteConfigException::class);
        $this->expectExceptionMessage($expectedMsg);
        $config->generateConfig();
    }

    /**
     * Return a new instance of a PHP/SAP config type B.
     * @param array|string|null $config PHP/SAP config JSON/array. Default: null
     * @return \phpsap\interfaces\IConfigB
     */
    abstract public function newConfigB($config = null);
}
