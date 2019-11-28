<?php

namespace phpsap\IntegrationTests;

use phpsap\classes\Config\ConfigCommon;
use phpsap\classes\Config\ConfigTypeA;
use phpsap\classes\Config\ConfigTypeB;
use phpsap\exceptions\ConnectionFailedException;
use phpsap\interfaces\Config\IConfigTypeA;
use phpsap\interfaces\Config\IConfigTypeB;
use phpsap\interfaces\IConnection;
use phpsap\interfaces\IFunction;

/**
 * Class \phpsap\IntegrationTests\AbstractConnectionTestCase
 *
 * Test connection class.
 *
 * @package phpsap\IntegrationTests
 * @author  Gregor J.
 * @license MIT
 */
abstract class AbstractConnectionTestCase extends AbstractTestCase
{
    /**
     * Test SAP RFC connection type A configuration.
     * @throws \InvalidArgumentException
     * @throws \PHPUnit_Framework_Exception
     */
    public function testConnectionConfigTypeA()
    {
        if (!extension_loaded($this->getModuleName())) {
            //load functions mocking SAP RFC module functions or class methods
            $this->mockConnectionFailed();
        }
        $conn = $this->newConnection(new ConfigTypeA([
            'ashost' => 'sap.example.com',
            'sysnr'  => '001',
            'client' => '002',
            'user'   => 'username',
            'passwd' => 'password'
        ]));
        static::assertInstanceOf(IConnection::class, $conn);
        /**
         * @var IConfigTypeA $cfg
         */
        $cfg = $conn->getConfiguration();
        static::assertInstanceOf(IConfigTypeA::class, $cfg);
        static::assertSame('sap.example.com', $cfg->getAshost());
        static::assertSame('001', $cfg->getSysnr());
        static::assertSame('002', $cfg->getClient());
        //Set a clearly non-existing hostname to cause a connection failure.
        $conn->getConfiguration()->setAshost('prod.sap.example.com');
        static::assertSame('prod.sap.example.com', $cfg->getAshost());
        /**
         * Try to establish a connection, which should fail because of example.com.
         */
        $exception = null;
        try {
            $conn->prepareFunction('RFC_PING');
        } catch (ConnectionFailedException $exception) {}
        static::assertInstanceOf(ConnectionFailedException::class, $exception);
    }

    /**
     * Test SAP RFC connection type B configuration.
     * @throws \InvalidArgumentException
     * @throws \PHPUnit_Framework_Exception
     */
    public function testConnectionConfigTypeB()
    {
        if (!extension_loaded($this->getModuleName())) {
            //load functions mocking SAP RFC module functions or class methods
            $this->mockConnectionFailed();
        }
        $conn = $this->newConnection(new ConfigTypeB([
            'mshost' => 'msg.sap.example.com',
            'group'  => 'grp01',
            'client' => '003',
            'user'   => 'username',
            'passwd' => 'password'
        ]));
        static::assertInstanceOf(IConnection::class, $conn);
        /**
         * @var IConfigTypeB $cfg
         */
        $cfg = $conn->getConfiguration();
        static::assertInstanceOf(IConfigTypeB::class, $cfg);
        static::assertSame('msg.sap.example.com', $cfg->getMshost());
        static::assertSame('grp01', $cfg->getGroup());
        static::assertSame('003', $cfg->getClient());
        $conn->getConfiguration()->setMshost('grp01msg.sap.example.com');
        static::assertSame('grp01msg.sap.example.com', $cfg->getMshost());
        $exception = null;
        try {
            $conn->prepareFunction('RFC_PING');
        } catch (ConnectionFailedException $exception) {}
        static::assertInstanceOf(ConnectionFailedException::class, $exception);
    }

    /**
     * Mock the SAP RFC module for a successful connection attempt.
     */
    abstract protected function mockSuccessfulConnect();

    /**
     * Test a successful connection attempt using either the module or its mockup.
     */
    public function testSuccessfulConnect()
    {
        if (!extension_loaded($this->getModuleName())) {
            //load functions mocking SAP RFC module functions or class methods
            $this->mockSuccessfulConnect();
            //load a bogus config
            $config = $this->getSampleSapConfig();
        } else {
            //load a valid config
            $config = $this->getSapConfig();
        }
        $rfc_ping = $this->newConnection($config)
            ->prepareFunction('RFC_PING');
        static::assertInstanceOf(IFunction::class, $rfc_ping);
    }

    /**
     * Mock the SAP RFC module for a failed connection attempt.
     */
    abstract protected function mockConnectionFailed();

    /**
     * Test a failed connection attempt using either the module or its mockup.
     * @expectedException \phpsap\exceptions\ConnectionFailedException
     */
    public function testConnectionFailed()
    {
        if (!extension_loaded($this->getModuleName())) {
            //load functions mocking SAP RFC module functions or class methods
            $this->mockConnectionFailed();
            //load a bogus config
            $config = $this->getSampleSapConfig();
        } else {
            //load a complete but invalid config
            $config = $this->getSampleSapConfig();
        }
        $rfc_ping = $this->newConnection($config)
            ->prepareFunction('RFC_PING');
    }

    /**
     * Data provider for incomplete configurations.
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function provideIncompleteConfig()
    {
        return [
            [new ConfigTypeA()],
            [new ConfigTypeA([
                ConfigTypeA::JSON_CLIENT => '001',
                ConfigTypeA::JSON_USER   => 'username',
                ConfigTypeA::JSON_PASSWD => 'password'
            ])],
            [new ConfigTypeA([
                ConfigTypeA::JSON_ASHOST => 'sap.example.com',
                ConfigTypeA::JSON_CLIENT => '001',
                ConfigTypeA::JSON_USER   => 'username',
                ConfigTypeA::JSON_PASSWD => 'password'
            ])],
            [new ConfigTypeA([
                ConfigTypeA::JSON_SYSNR  => '00',
                ConfigTypeA::JSON_CLIENT => '001',
                ConfigTypeA::JSON_USER   => 'username',
                ConfigTypeA::JSON_PASSWD => 'password'
            ])],
            [new ConfigTypeA([
                ConfigTypeA::JSON_GWHOST => 'sapgw.example.com',
                ConfigTypeA::JSON_CLIENT => '001',
                ConfigTypeA::JSON_USER   => 'username',
                ConfigTypeA::JSON_PASSWD => 'password'
            ])],
            [new ConfigTypeA([
                ConfigTypeA::JSON_GWSERV => 'sapsrv',
                ConfigTypeA::JSON_CLIENT => '001',
                ConfigTypeA::JSON_USER   => 'username',
                ConfigTypeA::JSON_PASSWD => 'password'
            ])],
            [new ConfigTypeB()],
            [new ConfigTypeB([
                ConfigTypeA::JSON_CLIENT => '001',
                ConfigTypeA::JSON_USER   => 'username',
                ConfigTypeA::JSON_PASSWD => 'password'
            ])],
            [new ConfigTypeB([
                ConfigTypeB::JSON_R3NAME => 'name',
                ConfigTypeA::JSON_CLIENT => '001',
                ConfigTypeA::JSON_USER   => 'username',
                ConfigTypeA::JSON_PASSWD => 'password'
            ])],
            [new ConfigTypeB([
                ConfigTypeB::JSON_GROUP  => 'group',
                ConfigTypeA::JSON_CLIENT => '001',
                ConfigTypeA::JSON_USER   => 'username',
                ConfigTypeA::JSON_PASSWD => 'password'
            ])]
        ];
    }

    /**
     * Test a failed connection attempt using either the module or its mockup.
     * @dataProvider provideIncompleteConfig
     * @expectedException \phpsap\exceptions\IncompleteConfigException
     * @expectedExceptionMessage Configuration is missing mandatory key
     */
    public function testIncompleteConfig($config)
    {
        /**
         * Connection with empty configuration will be considered incomplete.
         */
        $rfc_ping = $this->newConnection($config)
            ->prepareFunction('RFC_PING');
    }
}
