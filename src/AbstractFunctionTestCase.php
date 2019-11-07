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

use phpsap\classes\RemoteApi;
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
        $jsonFile = __DIR__ . DIRECTORY_SEPARATOR . 'RFC_WALK_THRU_TEST.json';
        $result = $this->newConnection($config)
            ->prepareFunction('RFC_WALK_THRU_TEST')
            ->setApi(new RemoteApi(file_get_contents($jsonFile)))
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
        /**
         * Assert float value.
         */
        static::assertArrayHasKey('RFCFLOAT', $test_out, 'Missing RFCFLOAT in TEST_OUT!');
        static::assertSame(70.11, $test_out['RFCFLOAT'], 'Test IN and OUT of RFCFLOAT don\'t match!');
        /**
         * Assert integer values.
         */
        static::assertArrayHasKey('RFCINT1', $test_out, 'Missing RFCINT1 in TEST_OUT!');
        static::assertSame(163, $test_out['RFCINT1'], 'Test IN and OUT of RFCINT1 don\'t match!');
        static::assertArrayHasKey('RFCINT2', $test_out, 'Missing RFCINT2 in TEST_OUT!');
        static::assertSame(5920, $test_out['RFCINT2'], 'Test IN and OUT of RFCINT2 don\'t match!');
        static::assertArrayHasKey('RFCINT4', $test_out, 'Missing RFCINT4 in TEST_OUT!');
        static::assertSame(416639, $test_out['RFCINT4'], 'Test IN and OUT of RFCINT4 don\'t match!');
        /**
         * Assert DateTime objects.
         */
        static::assertArrayHasKey('RFCTIME', $test_out, 'Missing RFCTIME in TEST_OUT!');
        static::assertInstanceOf(\DateTime::class, $test_out['RFCTIME'], 'Test OUT of RFCTIME is not DateTime!');
        static::assertSame($testDateTime->format('H:i:s'), $test_out['RFCTIME']->format('H:i:s'));
        static::assertArrayHasKey('RFCDATE', $test_out, 'Missing RFCDATE in TEST_OUT!');
        static::assertInstanceOf(\DateTime::class, $test_out['RFCDATE'], 'Test OUT of RFCDATE is not DateTime!');
        static::assertSame($testDateTime->format('Y-m-d'), $test_out['RFCDATE']->format('Y-m-d'));
        /**
         * Assert hexadecimal value.
         */
        static::assertArrayHasKey('RFCHEX3', $test_out, 'Missing RFCHEX3 in TEST_OUT!');
        static::assertSame('S', $test_out['RFCHEX3'], 'Test IN and OUT of RFCHEX3 don\'t match!');
        /**
         * Assert string values.
         */
        static::assertArrayHasKey('RFCCHAR1', $test_out, 'Missing RFCCHAR1 in TEST_OUT!');
        static::assertSame('A', $test_out['RFCCHAR1'], 'Test IN and OUT of RFCCHAR1 don\'t match!');
        static::assertArrayHasKey('RFCCHAR2', $test_out, 'Missing RFCCHAR2 in TEST_OUT!');
        static::assertSame('XC', $test_out['RFCCHAR2'], 'Test IN and OUT of RFCCHAR2 don\'t match!');
        static::assertArrayHasKey('RFCCHAR4', $test_out, 'Missing RFCCHAR4 in TEST_OUT!');
        static::assertSame('QqMh', $test_out['RFCCHAR4'], 'Test IN and OUT of RFCCHAR4 don\'t match!');
        static::assertArrayHasKey('RFCDATA1', $test_out, 'Missing RFCDATA1 in TEST_OUT!');
        static::assertSame('qKWjmNfad32rfS9Z', $test_out['RFCDATA1'], 'Test IN and OUT of RFCDATA1 don\'t match!');
        static::assertArrayHasKey('RFCDATA2', $test_out, 'Missing RFCDATA2 in TEST_OUT!');
        static::assertSame('xi82ph2zJ8BCVtlR', $test_out['RFCDATA2'], 'Test IN and OUT of RFCDATA2 don\'t match!');
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
        //try to invoke a function call using an invalid parameter value
        $jsonFile = __DIR__ . DIRECTORY_SEPARATOR . 'RFC_READ_TABLE.json';
        $this->newConnection($config)
            ->prepareFunction('RFC_READ_TABLE')
            ->setApi(new RemoteApi(file_get_contents($jsonFile)))
            ->setParam('QUERY_TABLE', '&')
            ->invoke();
    }
}
