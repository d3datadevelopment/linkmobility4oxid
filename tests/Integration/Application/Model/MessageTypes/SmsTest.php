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

namespace D3\Linkmobility4OXID\Tests\Integration\Application\Model\MessageTypes;

use D3\Linkmobility4OXID\Application\Model\Configuration;
use D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Remark;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\PayPalModule\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class SmsTest extends d3ModCfgUnitTestCase
{
    /** @var Sms */
    protected $model;
    protected $countryId = 'countryIdNo1';
    protected $userId = 'userIdNo1';
    protected $exampleNumber;

    public function setUp()
    {
        parent::setUp();

        $this->model = oxNew(Sms::class, 'demomessage');

        $this->addObjects();

        $phoneUtil = PhoneNumberUtil::getInstance();
        $example = $phoneUtil->getExampleNumberForType('DE', PhoneNumberType::MOBILE);
        $this->exampleNumber = $phoneUtil->format($example,  PhoneNumberFormat::NATIONAL);
    }

    public function addObjects()
    {
        $country = oxNew(Country::class);
        $country->setId($this->countryId);
        $country->assign([
            'oxisoalpha2'   => 'DE'
        ]);
        $country->save();

        //$user = new User();
        //$user->setId($this->userId);
        //$user->save();
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->clearObjects();

        unset($this->model);
    }

    public function clearObjects()
    {
        $country = oxNew(Country::class);
        $country->delete($this->countryId);

        $user = oxNew(User::class);
        $user->delete($this->userId);
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     */
    public function testSendOrderMessage()
    {
        Registry::getConfig()->setConfigParam(Configuration::DEBUG, true);

        /** @var User $user */
        //$user = oxNew(User::class);
        //$user->load($this->userId);

        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->setMethods([
                'getFieldData',
                'getOrderUser'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->method('getFieldData')->willReturnCallback([$this, 'orderFieldDataCallback']);
        $orderMock->method('getOrderUser')->willReturn($user);

        $this->assertTrue(
            $this->callMethod(
                $this->model,
                'sendOrderMessage',
                [$orderMock]
            )
        );

        $remark = oxNew(Remark::class);
dumpvar($remark);
        // $remarkId = "SELECT oxid FROM ".$remark->getViewName()." WHERE "
        // $this->assertTrue(

        // )
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