<?php

namespace phpsap\IntegrationTests;

use phpsap\classes\Config\ConfigTypeA;
use phpsap\classes\Config\ConfigTypeB;
use phpsap\DateTime\SapDateTime;
use phpsap\interfaces\Config\IConfigTypeA;
use phpsap\interfaces\Config\IConfigTypeB;
use phpsap\interfaces\exceptions\IConnectionFailedException;
use phpsap\interfaces\IFunction;

/**
 * Class phpsap\IntegrationTests\AbstractSapRfcTestCase
 *
 * Test the actual implementation of a SAP remote function call.
 *
 * @package phpsap\IntegrationTests
 * @author  Gregor J.
 * @license MIT
 */
abstract class AbstractSapRfcTestCase extends AbstractTestCase
{
    /**
     * Mock the SAP RFC module for a failed connection attempt.
     */
    abstract protected function mockConnectionFailed();

    /**
     * Test SAP RFC connection type A configuration.
     */
    public function testConnectionConfigTypeA()
    {
        if (!extension_loaded(static::getModuleName())) {
            //load functions mocking SAP RFC module functions or class methods
            $this->mockConnectionFailed();
        }
        $saprfc = static::newSapRfc('RFC_PING', null, new ConfigTypeA([
            'ashost' => 'sap.example.com',
            'sysnr'  => '001',
            'client' => '002',
            'user'   => 'username',
            'passwd' => 'password'
        ]), static::getApi('RFC_PING'));
        static::assertInstanceOf(IFunction::class, $saprfc);
        $cfg = $saprfc->getConfiguration();
        static::assertInstanceOf(IConfigTypeA::class, $cfg);
        static::assertSame('sap.example.com', $cfg->getAshost());
        static::assertSame('001', $cfg->getSysnr());
        static::assertSame('002', $cfg->getClient());
        //Set a clearly non-existing hostname to cause a connection failure.
        $saprfc->getConfiguration()->setAshost('prod.sap.example.com');
        static::assertSame('prod.sap.example.com', $cfg->getAshost());
        /**
         * Try to establish a connection, which should fail because of example.com.
         */
        $exception = null;
        try {
            $saprfc->invoke();
        } catch (IConnectionFailedException $exception) {
        }
        static::assertInstanceOf(IConnectionFailedException::class, $exception);
    }

    /**
     * Test SAP RFC connection type B configuration.
     */
    public function testConnectionConfigTypeB()
    {
        if (!extension_loaded(static::getModuleName())) {
            //load functions mocking SAP RFC module functions or class methods
            $this->mockConnectionFailed();
        }
        $saprfc = static::newSapRfc('RFC_PING', null, new ConfigTypeB([
            'mshost' => 'msg.sap.example.com',
            'group'  => 'grp01',
            'client' => '003',
            'user'   => 'username',
            'passwd' => 'password'
        ]), static::getApi('RFC_PING'));
        static::assertInstanceOf(IFunction::class, $saprfc);
        /**
         * @var IConfigTypeB $cfg
         */
        $cfg = $saprfc->getConfiguration();
        static::assertInstanceOf(IConfigTypeB::class, $cfg);
        static::assertSame('msg.sap.example.com', $cfg->getMshost());
        static::assertSame('grp01', $cfg->getGroup());
        static::assertSame('003', $cfg->getClient());
        $saprfc->getConfiguration()->setMshost('grp01msg.sap.example.com');
        static::assertSame('grp01msg.sap.example.com', $cfg->getMshost());
        /**
         * Try to establish a connection, which should fail because of example.com.
         */
        $exception = null;
        try {
            $saprfc->invoke();
        } catch (IConnectionFailedException $exception) {
        }
        static::assertInstanceOf(IConnectionFailedException::class, $exception);
    }

    /**
     * Mock the SAP RFC module for a successful connection attempt.
     */
    abstract protected function mockSuccessfulRfcPing();

    /**
     * Test a successful SAP remote function call to RFC_PING.
     */
    public function testSuccessfulRfcPing()
    {
        if (!extension_loaded(static::getModuleName())) {
            //load functions mocking SAP RFC module functions or class methods
            $this->mockSuccessfulRfcPing();
            //load a bogus config
            $config = static::getSampleSapConfig();
        } else {
            //load a valid config
            $config = static::getActualSapConfig();
        }
        $rfcPing = static::newSapRfc('RFC_PING', null, $config);
        static::assertInstanceOf(IFunction::class, $rfcPing);
        $result = $rfcPing->invoke();
        static::assertSame([], $result);
    }

    /**
     * Data provider for incomplete configurations.
     * @return array
     */
    public static function provideIncompleteConfig()
    {
        return [
            [null],
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
            ])],
            [new ConfigTypeB([
                ConfigTypeB::JSON_MSHOST => 'mshost.example.com',
                ConfigTypeB::JSON_LANG => 'DE',
                ConfigTypeB::JSON_TRACE => ConfigTypeB::TRACE_FULL
            ])]
        ];
    }

    /**
     * Test a failed connection attempt using either the module or its mockup.
     * @param \phpsap\interfaces\Config\IConfiguration $config
     * @dataProvider             provideIncompleteConfig
     * @expectedException \phpsap\exceptions\IncompleteConfigException
     */
    public function testIncompleteConfig($config)
    {
        /**
         * Connection with empty configuration will be considered incomplete.
         */
        static::newSapRfc('RFC_PING', null, $config, static::getApi('RFC_PING'))
            ->invoke();
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
        if (!extension_loaded(static::getModuleName())) {
            //load functions mocking SAP RFC module functions or class methods
            $this->mockUnknownFunctionException();
            //load a bogus config
            $config = static::getSampleSapConfig();
        } else {
            //load a valid config
            $config = static::getActualSapConfig();
        }
        static::newSapRfc('RFC_PINGG', null, $config)->invoke();
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
        if (!extension_loaded(static::getModuleName())) {
            //load functions mocking SAP RFC module functions or class methods
            $this->mockRemoteFunctionCallWithParametersAndResults();
            //load a bogus config
            $config = static::getSampleSapConfig();
        } else {
            //load a valid config
            $config = static::getActualSapConfig();
        }
        //prepare a DateTime object for testing SAP date and time.
        $testDateTime = new \DateTime('2019-10-30 10:20:30');
        //prepare function call parameter
        $test_in = [
            'RFCFLOAT' => 70.11,
            'RFCCHAR1' => 'A',
            'RFCINT2' => 4095,
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
        $result = static::newSapRfc('RFC_WALK_THRU_TEST')
            ->setConfiguration($config)
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
        static::assertSame(4095, $test_out['RFCINT2'], 'Test IN and OUT of RFCINT2 don\'t match!');
        static::assertArrayHasKey('RFCINT4', $test_out, 'Missing RFCINT4 in TEST_OUT!');
        static::assertSame(416639, $test_out['RFCINT4'], 'Test IN and OUT of RFCINT4 don\'t match!');
        /**
         * Assert DateTime objects.
         */
        static::assertArrayHasKey('RFCTIME', $test_out, 'Missing RFCTIME in TEST_OUT!');
        static::assertArrayHasKey('RFCDATE', $test_out, 'Missing RFCDATE in TEST_OUT!');
        static::assertArrayHasKey('RFCHEX3', $test_out, 'Missing RFCHEX3 in TEST_OUT!');
        /**
         * Assertions based on the capabilities of the underlying module.
         */
        if (is_string($test_out['RFCTIME'])) {
            static::assertSame('102030', $test_out['RFCTIME']);
            static::assertSame('20191030', $test_out['RFCDATE']);
            /**
             * Assert hexadecimal value.
             */
            static::assertSame('53', $test_out['RFCHEX3'], 'Test IN and OUT of RFCHEX3 don\'t match!');
        } else {
            static::assertInstanceOf(\DateTime::class, $test_out['RFCTIME'], 'Test OUT of RFCTIME is not DateTime!');
            static::assertSame($testDateTime->format('H:i:s'), $test_out['RFCTIME']->format('H:i:s'));
            static::assertInstanceOf(\DateTime::class, $test_out['RFCDATE'], 'Test OUT of RFCDATE is not DateTime!');
            static::assertSame($testDateTime->format('Y-m-d'), $test_out['RFCDATE']->format('Y-m-d'));
            /**
             * Assert hexadecimal value.
             */
            static::assertSame('S', $test_out['RFCHEX3'], 'Test IN and OUT of RFCHEX3 don\'t match!');
        }
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
        if (!extension_loaded(static::getModuleName())) {
            //load functions mocking SAP RFC module functions or class methods
            $this->mockFailedRemoteFunctionCallWithParameters();
            //load a bogus config
            $config = static::getSampleSapConfig();
        } else {
            //load a valid config
            $config = static::getActualSapConfig();
        }
        static::newSapRfc('RFC_READ_TABLE')
            ->setConfiguration($config)
            ->setParam('QUERY_TABLE', '&')
            ->invoke();
    }
}
