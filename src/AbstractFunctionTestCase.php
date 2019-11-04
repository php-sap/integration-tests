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

use phpsap\DateTime\SapDateTime;

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
        //prepare a DateTime object for testing SAP date and time.
        $testDateTime = new \DateTime('2019-10-30 10:20:30');
        //prepare function call parameter
        $test_in = [
            'RFCFLOAT' => 70.11,
            'RFCCHAR1' => 'A',
            'RFCINT2' => 5920,
            'RFCINT1' => 163,
            'RFCCHAR4' => 'QqMh',
            'RFCINT4' => 416639,
            'RFCHEX3' => '53', //=S
            'RFCCHAR2' => 'XC',
            'RFCTIME' => $testDateTime->format(SapDateTime::SAP_TIME),
            'RFCDATE' => $testDateTime->format(SapDateTime::SAP_DATE),
            'RFCDATA1' => 'qKWjmNfad32rfS9Z',
            'RFCDATA2' => 'xi82ph2zJ8BCVtlR'
        ];
        //remote function call
        $result = $this->newConnection($config)
            ->prepareFunction('RFC_WALK_THRU_TEST')
            ->setParam('TEST_IN', $test_in)
            ->setParam('DESTINATIONS', [
                ['RFCDEST' => 'AOP3']
            ])
            ->invoke();
        //assert basics
        static::assertInternalType('array', $result);
        static::assertArrayHasKey('TEST_OUT', $result);
        //create a link for programmer's convenience ...
        $test_out = &$result['TEST_OUT'];
        foreach ($test_in as $key => $value) {
            /**
             * Assert the existence of the key from test IN in test OUT.
             */
            static::assertArrayHasKey($key, $test_out, sprintf(
                'Missing key %s in TEST_OUT!',
                $key
            ));
            if ($key === 'RFCTIME') {
                /**
                 * Assert DateTime objects.
                 */
                static::assertInstanceOf(\DateTime::class, $test_out[$key], sprintf(
                    'Test OUT of key %s is not DateTime!',
                    $key
                ));
                //compare only time
                $format = 'H:i:s';
                static::assertSame($testDateTime->format($format), $test_out[$key]->format($format));
            }
            elseif ($key === 'RFCDATE') {
                /**
                 * Assert DateTime objects.
                 */
                static::assertInstanceOf(\DateTime::class, $test_out[$key], sprintf(
                    'Test OUT of key %s is not DateTime!',
                    $key
                ));
                //compare only date
                $format = 'Y-m-d';
                static::assertSame($testDateTime->format($format), $test_out[$key]->format($format));
            } elseif ($key === 'RFCHEX3') {
                /**
                 * Assert simple values.
                 */
                static::assertSame(hex2bin($value), $test_out[$key], sprintf(
                    'Test IN and OUT of HEX-key %s don\'t match!',
                    $key
                ));
            } else {
                /**
                 * Assert simple values.
                 */
                static::assertSame($value, $test_out[$key], sprintf(
                    'Test IN and OUT of key %s don\'t match!',
                    $key
                ));
            }
        }
        /**
         * Assert empty table parameter 'DESTINATIONS'.
         */
        static::assertArrayHasKey('DESTINATIONS', $result);
        static::assertEmpty($result['DESTINATIONS']);
        /**
         * Assert return table 'LOG' and that RFCDEST is the same as in the
         * 'DESTINATIONS' table parameter.
         */
        static::assertArrayHasKey('LOG', $result);
        static::assertArrayHasKey(0, $result['LOG']);
        static::assertInternalType('array', $result['LOG'][0]);
        static::assertArrayHasKey('RFCDEST', $result['LOG'][0]);
        static::assertSame('AOP3', $result['LOG'][0]['RFCDEST']);
    }

    /**
     * Mock the SAP RFC module for a failed SAP remote function call with parameters.
     */
    abstract protected function mockFailedRemoteFunctionCallWithParameters();

    /**
     * Test a failed remote function call with parameters.
     * @expectedException \phpsap\exceptions\FunctionCallException
     * @expectedExceptionMessageRegExp "^Function call RFC_READ_TABLE failed: .*"
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
        $function = $connection->prepareFunction('RFC_READ_TABLE');
        $function->setParam('QUERY_TABLE', '&');
        $function->invoke();
    }
}
