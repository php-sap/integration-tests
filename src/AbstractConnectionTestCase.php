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

use phpsap\saprfc\SapRfcConnection;

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
     * Load class mocking a successful connection attempt using \sapnwrfc.
     */
    abstract protected function mockSuccessfulConnect();

    /**
     * Run a successful connection attempt.
     */
    public function testSuccessfulConnect()
    {
        if (!extension_loaded($this->getModuleName())) {
            //load functions mocking saprfc module functions
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
     * Load class mocking a failed connection attempt using \sapnwrfc.
     */
    abstract protected function mockFailedConnect();

    /**
     * Run a failed connection attempt.
     * @expectedException \phpsap\exceptions\ConnectionFailedException
     */
    public function testFailedConnect()
    {
        if (!extension_loaded($this->getModuleName())) {
            //load functions mocking saprfc module functions
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
     * Load class mocking a successful connection ping using \sapnwrfc.
     */
    abstract protected function mockSuccessfulPing();

    /**
     * Successfully ping a connection.
     */
    public function testSuccessfulPing()
    {
        if (!extension_loaded($this->getModuleName())) {
            //load functions mocking saprfc module functions
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
     * Load class mocking a failed connection ping using \sapnwrfc.
     */
    abstract protected function mockFailedPing();

    /**
     * Fail to ping a connection.
     */
    public function testFailedPing()
    {
        if (!extension_loaded($this->getModuleName())) {
            //load functions mocking saprfc module functions
            $this->mockFailedPing();
            //load a bogus config
            $config = $this->getSampleSapConfig();
        } else {
            static::markTestSkipped('Cannot test a failing ping with saprfc loaded.');
        }
        $connection = $this->newConnection($config);
        $result = $connection->ping();
        static::assertFalse($result);
    }
}
