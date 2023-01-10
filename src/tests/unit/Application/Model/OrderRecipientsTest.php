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

use D3\Linkmobility4OXID\Application\Model\Configuration;
use D3\Linkmobility4OXID\Application\Model\Exceptions\noRecipientFoundException;
use D3\Linkmobility4OXID\Application\Model\OrderRecipients;
use D3\Linkmobility4OXID\tests\unit\LMUnitTestCase;
use D3\LinkmobilityClient\Exceptions\RecipientException;
use D3\LinkmobilityClient\ValueObject\Recipient;
use D3\TestingTools\Development\CanAccessRestricted;
use libphonenumber\NumberParseException;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class OrderRecipientsTest extends LMUnitTestCase
{
    use CanAccessRestricted;

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\OrderRecipients::__construct
     */
    public function canConstruct()
    {
        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var OrderRecipients|MockObject $sut */
        $sut = $this->getMockBuilder(OrderRecipients::class)
            ->setConstructorArgs([$orderMock])
            ->getMock();

        $this->assertSame(
            $orderMock,
            $this->getValue(
                $sut,
                'order'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @dataProvider canGetSmsRecipientDataProvider
     * @covers \D3\Linkmobility4OXID\Application\Model\OrderRecipients::getSmsRecipient
     */
    public function canGetSmsRecipient($hasRecipient)
    {
        /** @var Recipient|MockObject $recipient1Mock */
        $recipient1Mock = $this->getMockBuilder(Recipient::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Recipient|MockObject $recipient2Mock */
        $recipient2Mock = $this->getMockBuilder(Recipient::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var OrderRecipients|MockObject $sut */
        $sut = $this->getMockBuilder(OrderRecipients::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSmsRecipientFields', 'getSmsRecipientByField'])
            ->getMock();
        $sut->method('getSmsRecipientFields')->willReturn(['pfield1'    => 'cfield1', 'pfield2' => 'cfield2', 'pfield3' => 'cfield3']);
        $sut->expects($this->exactly($hasRecipient ? 2 : 3))->method('getSmsRecipientByField')->willReturnOnConsecutiveCalls(
            null,
            $hasRecipient ? $recipient1Mock : null,
            $hasRecipient ? $recipient2Mock : null
        );

        if (!$hasRecipient) {
            $this->expectException(noRecipientFoundException::class);
        }

        $this->assertSame(
            $recipient1Mock,
            $this->callMethod(
                $sut,
                'getSmsRecipient'
            )
        );
    }

    /**
     * @return array
     */
    public function canGetSmsRecipientDataProvider(): array
    {
        return [
            'has recipient'     => [true],
            'has no recipient'  => [false],
        ];
    }

    /**
     * @test
     * @param $orderFieldValue
     * @param $expected
     * @param $thrownExc
     * @return void
     * @throws ReflectionException
     * @dataProvider canGetSmsRecipientByFieldDataProvider
     * @covers       \D3\Linkmobility4OXID\Application\Model\OrderRecipients::getSmsRecipientByField
     */
    public function canGetSmsRecipientByField($orderFieldValue, $expected, $thrownExc)
    {
        /** @var Recipient|MockObject $recipientMock */
        $recipientMock = $this->getMockBuilder(Recipient::class)
            ->disableOriginalConstructor()
            ->getMock();
        d3GetOxidDIC()->set(Recipient::class, $recipientMock);

        /** @var Country|MockObject $countryMock */
        $countryMock = $this->getMockBuilder(Country::class)
            ->onlyMethods(['load', 'getFieldData'])
            ->getMock();
        $countryMock->method('load')->will(
            $thrownExc ?
                $this->throwException(d3GetOxidDIC()->get($thrownExc)) :
                $this->returnValue(true)
        );
        $countryMock->method('getFieldData')->willReturn('de');
        d3GetOxidDIC()->set('d3ox.linkmobility.'.Country::class, $countryMock);

        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFieldData'])
            ->getMock();
        $orderMock->method('getFieldData')->willReturnMap(
            [
                ['phoneFieldName', $orderFieldValue],
                ['oxcountryid', 'country_de'],
            ]
        );

        /** @var OrderRecipients|MockObject $sut */
        $sut = $this->getMockBuilder(OrderRecipients::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setValue(
            $sut,
            'order',
            $orderMock
        );

        $this->assertSame(
            $expected === 'recipientMock' ? $recipientMock : $expected,
            $this->callMethod(
                $sut,
                'getSmsRecipientByField',
                ['phoneFieldName', 'countryIdFieldName']
            )
        );
    }

    /**
     * @return array
     */
    public function canGetSmsRecipientByFieldDataProvider(): array
    {
        return [
            'has order field value no exc'   => ['fieldContent', 'recipientMock', false],
            'spaced order field value'       => ['    ', null, false],
            'no order field value'           => [null, null, false],
            'has order field value recexc'   => ['fieldContent', null, RecipientException::class],
            'has order field value nmbexc'   => ['fieldContent', null, NumberParseException::class],
        ];
    }

    /**
     * @test
     * @param $configuredFields
     * @param $expected
     * @return void
     * @throws ReflectionException
     * @dataProvider canGetSmsRecipientFieldsDataProvider
     * @covers \D3\Linkmobility4OXID\Application\Model\OrderRecipients::getSmsRecipientFields
     */
    public function canGetSmsRecipientFields($configuredFields, $expected)
    {
        /** @var Configuration|MockObject $configurationMock */
        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getOrderRecipientFields'])
            ->getMock();
        $configurationMock->method('getOrderRecipientFields')->willReturn($configuredFields);
        d3GetOxidDIC()->set(Configuration::class, $configurationMock);

        /** @var OrderRecipients|MockObject $sut */
        $sut = $this->getMockBuilder(OrderRecipients::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__construct'])
            ->getMock();

        $this->assertSame(
            $expected,
            $this->callMethod(
                $sut,
                'getSmsRecipientFields'
            )
        );
    }

    /**
     * @return array
     */
    public function canGetSmsRecipientFieldsDataProvider(): array
    {
        return [
            'fields configured'     => [['pfield1' => 'cfield1', 'pfield2' => 'cfield2'], ['pfield1' => 'cfield1', 'pfield2' => 'cfield2']],
            'fields not configured' => [[], ['oxdelfon' => 'oxdelcountryid', 'oxbillfon' => 'oxbillcountryid']],
        ];
    }
}
