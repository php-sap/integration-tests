<?php
/**
 * File src/AbstractConfigATestCase.php
 *
 * Test config type A.
 *
 * @package integration-tests
 * @author  Gregor J.
 * @license MIT
 */

namespace phpsap\IntegrationTests;

use phpsap\classes\AbstractConfigA;
use phpsap\exceptions\IncompleteConfigException;
use phpsap\interfaces\IConfig;
use phpsap\interfaces\IConfigA;
use PHPUnit\Framework\TestCase;

/**
 * Class \phpsap\IntegrationTests\AbstractConfigATestCase
 *
 * Test config type A.
 *
 * @package phpsap\IntegrationTests
 * @author  Gregor J.
 * @license MIT
 */
abstract class AbstractConfigATestCase extends TestCase
{
    /**
     * Test config type A inheritance chain.
     */
    public function testInheritance()
    {
        $config = $this->newConfigA();
        static::assertInstanceOf(IConfig::class, $config);
        static::assertInstanceOf(IConfigA::class, $config);
        static::assertInstanceOf(AbstractConfigA::class, $config);
    }

    /**
     * Test a valid config creation.
     */
    public function testValidConfig()
    {
        $configArr = [
            'ashost' => 'sap.example.com',
            'sysnr' => '000',
            'client' => '01',
            'user' => 'username',
            'passwd' => 'password',
            'gwhost' => 'gw.example.com',
            'gwserv' => 'abc',
            'lang' => 'EN',
            'trace' => IConfigA::TRACE_FULL
        ];
        $configJson = json_encode($configArr);
        $config = $this->newConfigA($configJson);
        $configSaprfc = $config->generateConfig();
        static::assertInternalType('array', $configSaprfc);
        static::assertArrayHasKey('ASHOST', $configSaprfc);
        static::assertSame('sap.example.com', $configSaprfc['ASHOST']);
        static::assertArrayHasKey('SYSNR', $configSaprfc);
        static::assertSame('000', $configSaprfc['SYSNR']);
        static::assertArrayHasKey('CLIENT', $configSaprfc);
        static::assertSame('01', $configSaprfc['CLIENT']);
        static::assertArrayHasKey('USER', $configSaprfc);
        static::assertSame('username', $configSaprfc['USER']);
        static::assertArrayHasKey('PASSWD', $configSaprfc);
        static::assertSame('password', $configSaprfc['PASSWD']);
        static::assertArrayHasKey('GWHOST', $configSaprfc);
        static::assertSame('gw.example.com', $configSaprfc['GWHOST']);
        static::assertArrayHasKey('GWSERV', $configSaprfc);
        static::assertSame('abc', $configSaprfc['GWSERV']);
        static::assertArrayHasKey('LANG', $configSaprfc);
        static::assertSame('EN', $configSaprfc['LANG']);
        static::assertArrayHasKey('TRACE', $configSaprfc);
        static::assertSame(IConfigA::TRACE_FULL, $configSaprfc['TRACE']);
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
                    'ashost' => 'sap.example.com',
                    'sysnr' => '000',
                    'client' => '01',
                    'user' => 'username'
                ],
                'passwd'
            ],
            [
                [
                    'ashost' => 'sap.example.com',
                    'sysnr' => '000',
                    'user' => 'username',
                    'passwd' => 'password',
                    'gwhost' => 'gw.example.com',
                    'gwserv' => 'abc',
                    'lang' => 'EN',
                    'trace' => IConfigA::TRACE_BRIEF
                ],
                'client'
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
        $config = $this->newConfigA($configJson);
        $expectedMsg = sprintf('Missing mandatory key %s.', $missing);
        $this->expectException(IncompleteConfigException::class);
        $this->expectExceptionMessage($expectedMsg);
        $config->generateConfig();
    }

    /**
     * Return a new instance of a PHP/SAP config type A.
     * @param array|string|null $config PHP/SAP config JSON/array. Default: null
     * @return \phpsap\interfaces\IConfigA
     */
    abstract public function newConfigA($config = null);
}
