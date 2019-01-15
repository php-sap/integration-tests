<?php
/**
 * File src/AbstractFunctionTestCase.php
 *
 * Test function class.
 *
 * @package integration-tests
 * @author  Gregor J.
 * @license MIT
 */

namespace phpsap\IntegrationTests;

/**
 * Class \phpsap\IntegrationTests\AbstractFunctionTestCase
 *
 * Test function class.
 *
 * @package phpsap\IntegrationTests
 * @author  Gregor J.
 * @license MIT
 */
abstract class AbstractFunctionTestCase extends AbstractTestCase
{

    /**
     * Mock the SAP RFC module for a successful SAP remote function call.
     */
    abstract protected function mockSuccessfulFunctionCall();

    /**
     * Test successfully invoking a SAP remote function call.
     */
    public function testSuccessfulFunctionCall()
    {
        if (!extension_loaded($this->getModuleName())) {
            //load functions mocking SAP RFC module functions or class methods
            $this->mockSuccessfulFunctionCall();
            //load a bogus config
            $config = $this->getSampleSapConfig();
        } else {
            //load a valid config
            $config = $this->getSapConfig();
        }
        $connection = $this->newConnection($config);
        $function = $connection->prepareFunction('RFC_PING');
        $result = $function->invoke();
        static::assertSame([], $result);
    }

    /**
     * Mock the SAP RFC module for an unknown function call exception.
     */
    abstract protected function mockUnknownFunctionException();

    /**
     * Test invoking an unknown function an receiving an exception.
     * @expectedException \phpsap\exceptions\UnknownFunctionException
     * @expectedExceptionMessageRegExp "^Unknown function RFC_PINGG: .*"
     */
    public function testUnknownFunctionException()
    {
        if (!extension_loaded($this->getModuleName())) {
            //load functions mocking SAP RFC module functions or class methods
            $this->mockUnknownFunctionException();
            //load a bogus config
            $config = $this->getSampleSapConfig();
        } else {
            //load a valid config
            $config = $this->getSapConfig();
        }
        $connection = $this->newConnection($config);
        $function = $connection->prepareFunction('RFC_PINGG');
        $function->invoke();
    }

    /**
     * Mock the SAP RFC module for a successful SAP remote function call with
     * parameters and results.
     */
    abstract protected function mockRemoteFunctionCallWithParametersAndResults();

    /**
     * Test successful SAP remote function call with parameters and results.
     */
    public function testRemoteFunctionCallWithParametersAndResults()
    {
        if (!extension_loaded($this->getModuleName())) {
            //load functions mocking SAP RFC module functions or class methods
            $this->mockRemoteFunctionCallWithParametersAndResults();
            //load a bogus config
            $config = $this->getSampleSapConfig();
        } else {
            //load a valid config
            $config = $this->getSapConfig();
        }
        $connection = $this->newConnection($config);
        $function = $connection->prepareFunction('Z_MC_GET_DATE_TIME');
        $function->setParam('IV_DATE', '20181119');
        $result = $function->invoke();
        $expected = [
            'EV_FRIDAY' => '20181123',
            'EV_FRIDAY_LAST' => '20181116',
            'EV_FRIDAY_NEXT' => '20181130',
            'EV_FRITXT' => 'Freitag',
            'EV_MONDAY' => '20181119',
            'EV_MONDAY_LAST' => '20181112',
            'EV_MONDAY_NEXT' => '20181126',
            'EV_MONTH' => '11',
            'EV_MONTH_LAST_DAY' => '20181130',
            'EV_MONTXT' => 'Montag',
            'EV_TIMESTAMP' => 'NOVALUE',
            'EV_WEEK' => '201847',
            'EV_WEEK_LAST' => '201846',
            'EV_WEEK_NEXT' => '201848',
            'EV_YEAR' => '2018'
        ];
        static::assertInternalType('array', $result);
        foreach ($expected as $name => $value) {
            static::assertArrayHasKey($name, $result);
            if ($name === 'EV_TIMESTAMP') {
                continue;
            }
            static::assertSame($value, $result[$name]);
        }
    }

    /**
     * Mock the SAP RFC module for a failed SAP remote function call with parameters.
     */
    abstract protected function mockFailedRemoteFunctionCallWithParameters();

    /**
     * Test a failed remote function call with parameters.
     * @expectedException \phpsap\exceptions\FunctionCallException
     * @expectedExceptionMessageRegExp "^Function call Z_MC_GET_DATE_TIME failed: .*"
     */
    public function testFailedRemoteFunctionCallWithParameters()
    {
        if (!extension_loaded($this->getModuleName())) {
            //load functions mocking SAP RFC module functions or class methods
            $this->mockFailedRemoteFunctionCallWithParameters();
            //load a bogus config
            $config = $this->getSampleSapConfig();
        } else {
            //load a valid config
            $config = $this->getSapConfig();
        }
        $connection = $this->newConnection($config);
        $function = $connection->prepareFunction('Z_MC_GET_DATE_TIME');
        $function->setParam('IV_DATE', '2018-11-19');
        $function->invoke();
    }
}
