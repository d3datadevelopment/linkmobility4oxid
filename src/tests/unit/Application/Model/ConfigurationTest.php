<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * https://www.d3data.de
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link      https://www.oxidmodule.com
 */

declare(strict_types=1);

namespace D3\Linkmobility4OXID\tests\unit\Application\Model;

use Assert\InvalidArgumentException;
use D3\Linkmobility4OXID\Application\Model\Configuration;
use D3\Linkmobility4OXID\tests\unit\LMUnitTestCase;
use D3\TestingTools\Development\CanAccessRestricted;
use OxidEsales\Eshop\Core\Config;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class ConfigurationTest extends LMUnitTestCase
{
    use CanAccessRestricted;

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @dataProvider canGetApiTokenDataProvider
     * @covers \D3\Linkmobility4OXID\Application\Model\Configuration::getApiToken
     */
    public function canGetApiToken($savedToken, $expected, $expectException)
    {
        /** @var Config|MockObject $configMock */
        $configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getConfigParam'])
            ->getMock();
        $configMock->method('getConfigParam')->willReturnCallback(
            function () use ($savedToken) {
                $aArgs = func_get_args();

                switch ($aArgs[0]) {
                    case Configuration::GENERAL_APITOKEN:
                        return $savedToken;
                }

                $this->fail('Unknown variable '.$aArgs[0]);
            }
        );

        /** @var Configuration|MockObject $sut */
        $sut = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getConfig'])
            ->getMock();
        $sut->method('getConfig')->willReturn($configMock);

        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $this->assertSame(
            $expected,
            $this->callMethod(
                $sut,
                'getApiToken'
            )
        );
    }

    /**
     * @return array[]
     */
    public function canGetApiTokenDataProvider(): array
    {
        return [
            'token ok'  => ['  apiTokenFixture  ', 'apiTokenFixture', false],
            'array token'  => [['apiTokenFixture'], 'aptTokenFixture', true],
            'null token'  => [null, 'aptTokenFixture', true],
            'empty token'  => ['', 'aptTokenFixture', true],
        ];
    }

    /**
     * @test
     * @param $testMode
     * @return void
     * @throws ReflectionException
     * @dataProvider trueFalseDataProvider
     * @covers \D3\Linkmobility4OXID\Application\Model\Configuration::getTestMode
     */
    public function canGetTestMode($testMode, $expected)
    {
        /** @var Config|MockObject $configMock */
        $configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getConfigParam'])
            ->getMock();
        $configMock->method('getConfigParam')->willReturnCallback(
            function () use ($testMode) {
                $aArgs = func_get_args();

                switch ($aArgs[0]) {
                    case Configuration::GENERAL_DEBUG:
                        return $testMode;
                }

                $this->fail('Unknown variable '.$aArgs[0]);
            }
        );

        /** @var Configuration|MockObject $sut */
        $sut = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getConfig'])
            ->getMock();
        $sut->method('getConfig')->willReturn($configMock);

        $this->assertSame(
            $expected,
            $this->callMethod(
                $sut,
                'getTestMode'
            )
        );
    }

    /**
     * @test
     * @param $config
     * @param $expected
     * @param $expectException
     * @return void
     * @throws ReflectionException
     * @dataProvider canGetSmsSenderNumberDataProvider
     * @covers       \D3\Linkmobility4OXID\Application\Model\Configuration::getSmsSenderNumber
     */
    public function canGetSmsSenderNumber($config, $expected, $expectException)
    {
        /** @var Config|MockObject $configMock */
        $configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getConfigParam'])
            ->getMock();
        $configMock->method('getConfigParam')->willReturnCallback(
            function () use ($config) {
                $aArgs = func_get_args();

                switch ($aArgs[0]) {
                    case Configuration::SMS_SENDERNR:
                        return $config;
                }

                $this->fail('Unknown variable '.$aArgs[0]);
            }
        );

        /** @var Configuration|MockObject $sut */
        $sut = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getConfig'])
            ->getMock();
        $sut->method('getConfig')->willReturn($configMock);

        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $this->assertSame(
            $expected,
            $this->callMethod(
                $sut,
                'getSmsSenderNumber'
            )
        );
    }

    /**
     * @return array[]
     */
    public function canGetSmsSenderNumberDataProvider(): array
    {
        return [
            'number ok'  => ['  0123456789  ', '0123456789', false],
            'array number'  => [['0123456789'], '0123456789', true],
            'null number'  => [null, null, true],
            'empty number'  => ['', null, false],
        ];
    }

    /**
     * @test
     * @param $config
     * @param $expected
     * @param $expectException
     * @return void
     * @throws ReflectionException
     * @dataProvider canGetSmsSenderCountryDataProvider
     * @covers       \D3\Linkmobility4OXID\Application\Model\Configuration::getSmsSenderCountry
     */
    public function canGetSmsSenderCountry($config, $expected, $expectException)
    {
        /** @var Config|MockObject $configMock */
        $configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getConfigParam'])
            ->getMock();
        $configMock->method('getConfigParam')->willReturnCallback(
            function () use ($config) {
                $aArgs = func_get_args();

                switch ($aArgs[0]) {
                    case Configuration::SMS_SENDERCOUNTRY:
                        return $config;
                }

                $this->fail('Unknown variable '.$aArgs[0]);
            }
        );

        /** @var Configuration|MockObject $sut */
        $sut = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getConfig'])
            ->getMock();
        $sut->method('getConfig')->willReturn($configMock);

        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $this->assertSame(
            $expected,
            $this->callMethod(
                $sut,
                'getSmsSenderCountry'
            )
        );
    }

    /**
     * @return array[]
     */
    public function canGetSmsSenderCountryDataProvider(): array
    {
        return [
            'country ok'        => ['  at  ', 'AT', false],
            'country to short'  => ['  D  ', 'D', true],
            'country to long'  => ['  FRA  ', 'FRA', true],
            'array country'  => [['DE'], 'DE', true],
            'null country'  => [null, null, true],
            'empty number'  => ['', null, false],
        ];
    }

    /**
     * @test
     * @param $config
     * @param $expected
     * @param $expectException
     * @return void
     * @throws ReflectionException
     * @dataProvider canGetOrderRecipientFieldsDataProvider
     * @covers       \D3\Linkmobility4OXID\Application\Model\Configuration::getOrderRecipientFields
     * @covers       \D3\Linkmobility4OXID\Application\Model\Configuration::checkFieldExists
     * @covers       \D3\Linkmobility4OXID\Application\Model\Configuration::sanitizeKeys
     */
    public function canGetOrderRecipientFields($config, $expected, $expectException)
    {
        /** @var Config|MockObject $configMock */
        $configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getConfigParam'])
            ->getMock();
        $configMock->method('getConfigParam')->willReturnCallback(
            function () use ($config) {
                $aArgs = func_get_args();

                switch ($aArgs[0]) {
                    case Configuration::ORDER_RECFIELDS:
                        return $config;
                }

                $this->fail('Unknown variable '.$aArgs[0]);
            }
        );

        /** @var Configuration|MockObject $sut */
        $sut = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getConfig'])
            ->getMock();
        $sut->method('getConfig')->willReturn($configMock);

        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $this->assertSame(
            $expected,
            $this->callMethod(
                $sut,
                'getOrderRecipientFields'
            )
        );
    }

    /**
     * @return array[]
     */
    public function canGetOrderRecipientFieldsDataProvider(): array
    {
        return [
            'string fields'         => ['oxbillfon', '', true],
            'array fields exist'    => [['  oxbillcountryid  ' => '  oxbillfon  ', '  oxdelcountryid  ' => '  oxdelfon  '], ['oxbillcountryid' => 'oxbillfon', 'oxdelcountryid' => 'oxdelfon'], false],
            'array fields not exist'=> [['  d3custfield1  ' => '  oxbillfon  ', '  oxdelcountryid  ' => '  d3custfield2  '], [], false],
            'empty number'          => [[], [], false],
        ];
    }

    /**
     * @test
     * @param $config
     * @param $expected
     * @param $expectException
     * @return void
     * @throws ReflectionException
     * @dataProvider canGetUserRecipientFieldsDataProvider
     * @covers       \D3\Linkmobility4OXID\Application\Model\Configuration::getUserRecipientFields
     * @covers       \D3\Linkmobility4OXID\Application\Model\Configuration::checkFieldExists
     * @covers       \D3\Linkmobility4OXID\Application\Model\Configuration::sanitizeKeys
     */
    public function canGetUserRecipientFields($config, $expected, $expectException)
    {
        /** @var Config|MockObject $configMock */
        $configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getConfigParam'])
            ->getMock();
        $configMock->method('getConfigParam')->willReturnCallback(
            function () use ($config) {
                $aArgs = func_get_args();

                switch ($aArgs[0]) {
                    case Configuration::USER_RECFIELDS:
                        return $config;
                }

                $this->fail('Unknown variable '.$aArgs[0]);
            }
        );

        /** @var Configuration|MockObject $sut */
        $sut = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getConfig'])
            ->getMock();
        $sut->method('getConfig')->willReturn($configMock);

        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $this->assertSame(
            $expected,
            $this->callMethod(
                $sut,
                'getUserRecipientFields'
            )
        );
    }

    /**
     * @return array[]
     */
    public function canGetUserRecipientFieldsDataProvider(): array
    {
        return [
            'string fields'         => ['oxfon', '', true],
            'array fields exist'    => [['  oxfon  ', '  oxmobfon  '], ['oxfon', 'oxmobfon'], false],
            'array fields not exist'=> [['  d3custfield1  ', '  d3custfield2  '], [], false],
            'empty number'          => [[], [], false],
        ];
    }

    /**
     * @test
     * @param $config
     * @param $expected
     * @return void
     * @throws ReflectionException
     * @dataProvider trueFalseDataProvider
     * @covers \D3\Linkmobility4OXID\Application\Model\Configuration::sendOrderFinishedMessage
     */
    public function canGetSendOrderFinishedMessage($config, $expected)
    {
        /** @var Config|MockObject $configMock */
        $configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getConfigParam'])
            ->getMock();
        $configMock->method('getConfigParam')->willReturnCallback(
            function () use ($config) {
                $aArgs = func_get_args();

                switch ($aArgs[0]) {
                    case Configuration::SENDBY_ORDERED:
                        return $config;
                }

                $this->fail('Unknown variable '.$aArgs[0]);
            }
        );

        /** @var Configuration|MockObject $sut */
        $sut = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getConfig'])
            ->getMock();
        $sut->method('getConfig')->willReturn($configMock);

        $this->assertSame(
            $expected,
            $this->callMethod(
                $sut,
                'sendOrderFinishedMessage'
            )
        );
    }

    /**
     * @test
     * @param $config
     * @param $expected
     * @return void
     * @throws ReflectionException
     * @dataProvider trueFalseDataProvider
     * @covers \D3\Linkmobility4OXID\Application\Model\Configuration::sendOrderSendedNowMessage
     */
    public function canGetSendOrderSendedNowMessage($config, $expected)
    {
        /** @var Config|MockObject $configMock */
        $configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getConfigParam'])
            ->getMock();
        $configMock->method('getConfigParam')->willReturnCallback(
            function () use ($config) {
                $aArgs = func_get_args();

                switch ($aArgs[0]) {
                    case Configuration::SENDBY_SENDEDNOW:
                        return $config;
                }

                $this->fail('Unknown variable '.$aArgs[0]);
            }
        );

        /** @var Configuration|MockObject $sut */
        $sut = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getConfig'])
            ->getMock();
        $sut->method('getConfig')->willReturn($configMock);

        $this->assertSame(
            $expected,
            $this->callMethod(
                $sut,
                'sendOrderSendedNowMessage'
            )
        );
    }

    /**
     * @test
     * @param $config
     * @param $expected
     * @return void
     * @throws ReflectionException
     * @dataProvider trueFalseDataProvider
     * @covers \D3\Linkmobility4OXID\Application\Model\Configuration::sendOrderCanceledMessage
     */
    public function canGetSendOrderCanceledMessage($config, $expected)
    {
        /** @var Config|MockObject $configMock */
        $configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getConfigParam'])
            ->getMock();
        $configMock->method('getConfigParam')->willReturnCallback(
            function () use ($config) {
                $aArgs = func_get_args();

                switch ($aArgs[0]) {
                    case Configuration::SENDBY_CANCELED:
                        return $config;
                }

                $this->fail('Unknown variable '.$aArgs[0]);
            }
        );

        /** @var Configuration|MockObject $sut */
        $sut = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getConfig'])
            ->getMock();
        $sut->method('getConfig')->willReturn($configMock);

        $this->assertSame(
            $expected,
            $this->callMethod(
                $sut,
                'sendOrderCanceledMessage'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\Configuration::getConfig
     */
    public function canGetConfig()
    {
        /** @var Configuration|MockObject $sut */
        $sut = $this->getMockBuilder(Configuration::class)
            ->getMock();

        $this->assertInstanceOf(
            Config::class,
            $this->callMethod(
                $sut,
                'getConfig'
            )
        );
    }

    /**
     * @return array
     */
    public function trueFalseDataProvider(): array
    {
        return [
            'true value'    => [true, true],
            'false value'   => [false, false],
            'one value'     => [1, true],
            'zero value'    => [0, false]
        ];
    }
}
