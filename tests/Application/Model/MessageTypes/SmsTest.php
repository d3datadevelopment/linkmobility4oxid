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

namespace D3\Linkmobility4OXID\Tests\Application\Model\MessageTypes;

use D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class SmsTest extends d3ModCfgUnitTestCase
{
    /** @var Sms */
    protected $model;
    protected $countryId = 'countryIdNo1';
    protected $exampleNumber;

    public function setUp()
    {
        parent::setUp();

        $this->model = oxNew(Sms::class, 'demomessage');

        $country = oxNew(Country::class);
        $country->setId($this->countryId);
        $country->assign([
            'oxisoalpha2'   => 'DE'
        ]);
        $country->save();

        $phoneUtil = PhoneNumberUtil::getInstance();
        $example = $phoneUtil->getExampleNumberForType('DE', PhoneNumberType::MOBILE);
        $this->exampleNumber = $phoneUtil->format($example,  PhoneNumberFormat::NATIONAL);
    }

    public function tearDown()
    {
        parent::tearDown();

        $country = oxNew(Country::class);
        $country->delete($this->countryId);

        unset($this->model);
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     */
    public function testSendOrderMessage()
    {
        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->setMethods(['getFieldData'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->method('getFieldData')->willReturnCallback([$this, 'orderFieldDataCallback']);

        $this->assertTrue(
            $this->callMethod(
                $this->model,
                'sendOrderMessage',
                [$orderMock]
            )
        );
    }

    public function orderFieldDataCallback()
    {
        $aArgs = func_get_args();

        switch ($aArgs[0]) {
            case 'oxdelfon':
            case 'oxbillfon':
                return $this->exampleNumber;
            case 'oxdelcountryid':
                return $this->countryId;
        }

        $this->fail('Unknown variable '.$aArgs[0]);
    }
}