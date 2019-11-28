<?php

namespace phpsap\IntegrationTests;

use phpsap\classes\Config\ConfigTypeA;
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
     * Test a failed connection attempt using either the module or its mockup.
     * @expectedException \phpsap\exceptions\IncompleteConfigException
     */
    public function testConfigIncomplete()
    {
        /**
         * Connection with empty configuration will be considered incomplete.
         */
        $rfc_ping = $this->newConnection()
            ->prepareFunction('RFC_PING');
    }
}
