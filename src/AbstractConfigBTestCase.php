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

/**
 * Class \phpsap\IntegrationTests\AbstractConfigBTestCase
 *
 * Test config type B.
 *
 * @package phpsap\IntegrationTests
 * @author  Gregor J.
 * @license MIT
 */
abstract class AbstractConfigBTestCase extends \PHPUnit_Framework_TestCase
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
        $this->assertValidModuleConfig(
            $config->generateConfig(),
            '02',
            'username',
            'password',
            'sap.example.com',
            'system_id',
            'logon_group',
            'EN',
            IConfigB::TRACE_VERBOSE
        );
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
        $this->setExpectedException(IncompleteConfigException::class, $expectedMsg);
        $config->generateConfig();
    }

    /**
     * Return a new instance of a PHP/SAP config type B.
     * @param array|string|null $config PHP/SAP config JSON/array. Default: null
     * @return \phpsap\interfaces\IConfigB
     */
    abstract public function newConfigB($config = null);

    /**
     * Assert the actual module configuration variable.
     * @param mixed $configSaprfc
     * @param string $client
     * @param string $user
     * @param string $passwd
     * @param string $mshost
     * @param string $r3name
     * @param string $group
     * @param string $lang
     * @param int $trace
     */
    abstract public function assertValidModuleConfig(
        $configSaprfc,
        $client,
        $user,
        $passwd,
        $mshost,
        $r3name,
        $group,
        $lang,
        $trace
    );
}
