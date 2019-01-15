<?php
/**
 * File src/AbstractConnectionTestCase.php
 *
 * Test connection class.
 *
 * @package integration-tests
 * @author  Gregor J.
 * @license MIT
 */

namespace phpsap\IntegrationTests;

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
        $connection = $this->newConnection($config);
        static::assertFalse($connection->isConnected());
        $connection->connect();
        static::assertTrue($connection->isConnected());
        $connection->connect();
        static::assertTrue($connection->isConnected());
        $connection->close();
        static::assertFalse($connection->isConnected());
    }

    /**
     * Mock the SAP RFC module for a failed connection attempt.
     */
    abstract protected function mockFailedConnect();

    /**
     * Test a failed connection attempt using either the module or its mockup.
     * @expectedException \phpsap\exceptions\ConnectionFailedException
     */
    public function testFailedConnect()
    {
        if (!extension_loaded($this->getModuleName())) {
            //load functions mocking SAP RFC module functions or class methods
            $this->mockFailedConnect();
            //load a bogus config
            $config = $this->getSampleSapConfig();
        } else {
            //load an invalid config
            $config = $this->getSampleSapConfig();
        }
        $connection = $this->newConnection($config);
        $connection->connect();
    }

    /**
     * Mock the SAP RFC module for a successful attempt to ping a connection.
     */
    abstract protected function mockSuccessfulPing();

    /**
     * Test a successful attempt to ping a connection using either the module or its
     * mockup.
     */
    public function testSuccessfulPing()
    {
        if (!extension_loaded($this->getModuleName())) {
            //load functions mocking SAP RFC module functions or class methods
            $this->mockSuccessfulPing();
            //load a bogus config
            $config = $this->getSampleSapConfig();
        } else {
            //load a valid config
            $config = $this->getSapConfig();
        }
        $connection = $this->newConnection($config);
        $result = $connection->ping();
        static::assertTrue($result);
    }

    /**
     * Mock the SAP RFC module for a failed attempt to ping a connection.
     */
    abstract protected function mockFailedPing();

    /**
     * Test a failed attempt to ping a connection using either the module or its
     * mockup.
     */
    public function testFailedPing()
    {
        if (!extension_loaded($this->getModuleName())) {
            //load functions mocking SAP RFC module functions or class methods
            $this->mockFailedPing();
            //load a bogus config
            $config = $this->getSampleSapConfig();
        } else {
            static::markTestSkipped('Cannot test a failing ping with SAP module loaded.');
        }
        $connection = $this->newConnection($config);
        $result = $connection->ping();
        static::assertFalse($result);
    }
}
