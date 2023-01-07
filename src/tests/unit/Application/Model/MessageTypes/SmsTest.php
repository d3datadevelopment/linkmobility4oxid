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

namespace D3\Linkmobility4OXID\tests\unit\Application\Model\MessageTypes;

use D3\Linkmobility4OXID\Application\Model\Configuration;
use D3\Linkmobility4OXID\Application\Model\Exceptions\noRecipientFoundException;
use D3\Linkmobility4OXID\Application\Model\MessageClient;
use D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms;
use D3\Linkmobility4OXID\Application\Model\OrderRecipients;
use D3\Linkmobility4OXID\Application\Model\RequestFactory;
use D3\Linkmobility4OXID\Application\Model\UserRecipients;
use D3\Linkmobility4OXID\tests\unit\LMUnitTestCase;
use D3\LinkmobilityClient\Client;
use D3\LinkmobilityClient\Exceptions\ApiException;
use D3\LinkmobilityClient\RecipientsList\RecipientsList;
use D3\LinkmobilityClient\SMS\BinaryRequest;
use D3\LinkmobilityClient\SMS\Response;
use D3\LinkmobilityClient\ValueObject\Recipient;
use D3\TestingTools\Development\CanAccessRestricted;
use Monolog\Logger;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Remark;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\UtilsView;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use ReflectionException;

class SmsTest extends LMUnitTestCase
{
    use CanAccessRestricted;

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms::__construct
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms::getMessage
     */
    public function canConstruct()
    {
        /** @var Sms|MockObject $sut */
        $sut = $this->getMockBuilder(Sms::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sanitizeMessage'])
            ->getMock();
        $sut->expects($this->once())->method('sanitizeMessage')->willReturnArgument(0);

        $this->callMethod(
            $sut,
            '__construct',
            ['messageFixture']
        );

        $this->assertSame(
            'messageFixture',
            $this->callMethod(
                $sut,
                'getMessage'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms::setRemark
     */
    public function canSetRemark()
    {
        /** @var Remark|MockObject $remarkMock */
        $remarkMock = $this->getMockBuilder(Remark::class)
            ->onlyMethods(['assign', 'save'])
            ->getMock();
        $remarkMock->expects($this->atLeastOnce())->method('assign');
        $remarkMock->expects($this->atLeastOnce())->method('save')->willReturn(true);
        d3GetOxidDIC()->set('d3ox.linkmobility.'.Remark::class, $remarkMock);

        /** @var Sms|MockObject $sut */
        $sut = $this->getMockBuilder(Sms::class)
            ->setConstructorArgs(['messageFixture'])
            ->onlyMethods(['getTypeName'])
            ->getMock();
        $sut->method('getTypeName')->willReturn('typeFixture');

        $this->callMethod(
            $sut,
            'setRemark',
            ['userIdFixture', 'recipients', 'messageFixture']
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms::getResponse
     */
    public function canGetResponse()
    {
        /** @var Response|MockObject $responseMock */
        $responseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Sms $sut */
        $sut = oxNew(Sms::class, 'messageFixture');

        $this->setValue(
            $sut,
            'response',
            $responseMock
        );

        $this->assertSame(
            $responseMock,
            $this->callMethod(
                $sut,
                'getResponse'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms::setRecipients
     */
    public function canSetRecipients()
    {
        /** @var RecipientsList|MockObject $recipientsListMock */
        $recipientsListMock = $this->getMockBuilder(RecipientsList::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Sms $sut */
        $sut = oxNew(Sms::class, 'messageFixture');

        $this->callMethod(
            $sut,
            'setRecipients',
            [$recipientsListMock]
        );

        $this->assertSame(
            $recipientsListMock,
            $this->getValue(
                $sut,
                'recipients'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms::getRecipientsList
     */
    public function canGetRecipientsList()
    {
        /** @var Recipient|MockObject $recipientsMock1 */
        $recipientsMock1 = $this->getMockBuilder(Recipient::class)
            ->setConstructorArgs(['01512 3456788', 'DE'])
            ->onlyMethods(['getFormatted'])
            ->getMock();

        /** @var Recipient|MockObject $recipientsMock1 */
        $recipientsMock2 = $this->getMockBuilder(Recipient::class)
            ->setConstructorArgs(['01512 3456789', 'DE'])
            ->onlyMethods(['getFormatted'])
            ->getMock();

        /** @var RecipientsList|MockObject $recipientsListMock */
        $recipientsListMock = $this->getMockBuilder(RecipientsList::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['clearRecipents'])
            ->getMock();
        $recipientsListMock->add($recipientsMock1)->add($recipientsMock2);

        /** @var Sms $sut */
        $sut = oxNew(Sms::class, 'messageFixture');
        $this->callMethod(
            $sut,
            'setRecipients',
            [$recipientsListMock]
        );

        $this->assertSame(
            '+4915123456788, +4915123456789',
            $this->callMethod(
                $sut,
                'getRecipientsList'
            )
        );
    }

    /**
     * @test
     * @param $message
     * @param $removeLineBreaks
     * @param $removeMultiSpaces
     * @param $expected
     * @return void
     * @throws ReflectionException
     * @dataProvider canSanitizeMessageDataProvider
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms::sanitizeMessage
     */
    public function canSanitizeMessage($message, $removeLineBreaks, $removeMultiSpaces, $expected)
    {
        /** @var Sms|MockObject $sut */
        $sut = $this->getMockBuilder(Sms::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setValue(
            $sut,
            'removeLineBreaks',
            $removeLineBreaks
        );

        $this->setValue(
            $sut,
            'removeMultipleSpaces',
            $removeMultiSpaces
        );

        $this->assertSame(
            $expected,
            $this->callMethod(
                $sut,
                'sanitizeMessage',
                [$message]
            )
        );
    }

    /**
     * @return array[]
     */
    public function canSanitizeMessageDataProvider(): array
    {
        $message = "  ab<br>cd   ef\r\ngh ";

        return [
            'keep linebreaks, keep multispaces' => [$message, false, false, "abcd   ef\r\ngh"],
            'rem linebreaks, keep multispaces' => [$message, true, false, "abcd   ef  gh"],
            'keep linebreaks, rem multispaces' => [$message, false, true, "abcd ef\r\ngh"],
            'rem linebreaks, rem multispaces' => [$message, true, true, 'abcd ef gh'],
        ];
    }

    /**
     * @test
     * @param $sendReturn
     * @param $throwException
     * @param $setRemark
     * @return void
     * @throws ReflectionException
     * @dataProvider canSendUserAccountMessageDataProvider
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms::sendUserAccountMessage
     */
    public function canSendUserAccountMessage($sendReturn, $throwException, $setRemark)
    {
        /** @var User|MockObject $userMock */
        $userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();
        $userMock->method('getId')->willReturn('userIdFixture');

        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockBuilder(Logger::class)
            ->onlyMethods(['warning'])
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock->expects($this->exactly((int) $throwException))->method('warning');

        /** @var UtilsView|MockObject $utilsViewMock */
        $utilsViewMock = $this->getMockBuilder(UtilsView::class)
            ->onlyMethods(['addErrorToDisplay'])
            ->getMock();
        $utilsViewMock->expects($this->exactly((int) $throwException))->method('addErrorToDisplay');
        d3GetOxidDIC()->set('d3ox.linkmobility.'.UtilsView::class, $utilsViewMock);

        /** @var RecipientsList|MockObject $recipientsListMock */
        $recipientsListMock = $this->getMockBuilder(RecipientsList::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Sms|MockObject $sut */
        $sut = $this->getMockBuilder(Sms::class)
            ->onlyMethods(['getLogger', 'sendCustomRecipientMessage', 'getUserRecipientsList', 'setRemark',
                'getRecipientsList', 'getMessage'])
            ->disableOriginalConstructor()
            ->getMock();
        $sut->method('getLogger')->willReturn($loggerMock);
        $sut->method('sendCustomRecipientMessage')->will(
            $throwException ?
                $this->throwException(oxNew(noRecipientFoundException::class)) :
                $this->returnValue($sendReturn)
        );
        $sut->expects($setRemark ? $this->once() : $this->never())->method('setRemark');
        $sut->method('getUserRecipientsList')->willReturn($recipientsListMock);
        $sut->method('getRecipientsList')->willReturn('abc,def');
        $sut->method('getMessage')->willReturn('messageFixture');

        $this->assertSame(
            $sendReturn,
            $this->callMethod(
                $sut,
                'sendUserAccountMessage',
                [$userMock]
            )
        );
    }

    /**
     * @return array
     */
    public function canSendUserAccountMessageDataProvider(): array
    {
        return [
            'can send'      => [true, false, true],
            'cant send'     => [false, false, false],
            'no recipient'  => [false, true, false]
        ];
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms::getUserRecipientsList
     */
    public function canGetUserRecipientsList()
    {
        /** @var User|MockObject $userMock */
        $userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Recipient|MockObject $recipientMock */
        $recipientMock = $this->getMockBuilder(Recipient::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var RecipientsList|MockObject $recipientsListMock */
        $recipientsListMock = $this->getMockBuilder(RecipientsList::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['add'])
            ->getMock();
        $recipientsListMock->expects($this->once())->method('add')->willReturnSelf();
        d3GetOxidDIC()->set(RecipientsList::class, $recipientsListMock);

        $userRecipientsMock = $this->getMockBuilder(UserRecipients::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSmsRecipient'])
            ->getMock();
        $userRecipientsMock->method('getSmsRecipient')->willReturn($recipientMock);
        d3GetOxidDIC()->set(UserRecipients::class, $userRecipientsMock);

        /** @var MessageClient|MockObject $messageClientMock */
        $messageClientMock = $this->getMockBuilder(MessageClient::class)
            ->getMock();
        d3GetOxidDIC()->set(MessageClient::class, $messageClientMock);

        /** @var Sms|MockObject $sut */
        $sut = $this->getMockBuilder(Sms::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertSame(
            $recipientsListMock,
            $this->callMethod(
                $sut,
                'getUserRecipientsList',
                [$userMock]
            )
        );
    }

    /**
     * @test
     * @param $sendReturn
     * @param $throwException
     * @param $setRemark
     * @return void
     * @throws ReflectionException
     * @dataProvider canSendUserAccountMessageDataProvider
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms::sendOrderMessage
     */
    public function canSendOrderMessage($sendReturn, $throwException, $setRemark)
    {
        /** @var User|MockObject $userMock */
        $userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();
        $userMock->method('getId')->willReturn('userIdFixture');

        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getOrderUser'])
            ->getMock();
        $orderMock->method('getId')->willReturn('userIdFixture');
        $orderMock->method('getOrderUser')->willReturn($userMock);

        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockBuilder(Logger::class)
            ->onlyMethods(['warning'])
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock->expects($this->exactly((int) $throwException))->method('warning');

        /** @var UtilsView|MockObject $utilsViewMock */
        $utilsViewMock = $this->getMockBuilder(UtilsView::class)
            ->onlyMethods(['addErrorToDisplay'])
            ->getMock();
        $utilsViewMock->expects($this->exactly((int) $throwException))->method('addErrorToDisplay');
        d3GetOxidDIC()->set('d3ox.linkmobility.'.UtilsView::class, $utilsViewMock);

        /** @var RecipientsList|MockObject $recipientsListMock */
        $recipientsListMock = $this->getMockBuilder(RecipientsList::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Sms|MockObject $sut */
        $sut = $this->getMockBuilder(Sms::class)
            ->onlyMethods(['getLogger', 'sendCustomRecipientMessage', 'getOrderRecipientsList', 'setRemark',
                'getRecipientsList', 'getMessage'])
            ->disableOriginalConstructor()
            ->getMock();
        $sut->method('getLogger')->willReturn($loggerMock);
        $sut->method('sendCustomRecipientMessage')->will(
            $throwException ?
                $this->throwException(oxNew(noRecipientFoundException::class)) :
                $this->returnValue($sendReturn)
        );
        $sut->expects($setRemark ? $this->once() : $this->never())->method('setRemark');
        $sut->method('getOrderRecipientsList')->willReturn($recipientsListMock);
        $sut->method('getRecipientsList')->willReturn('abc,def');
        $sut->method('getMessage')->willReturn('messageFixture');

        $this->assertSame(
            $sendReturn,
            $this->callMethod(
                $sut,
                'sendOrderMessage',
                [$orderMock]
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms::getOrderRecipientsList
     */
    public function canGetOrderRecipientsList()
    {
        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Recipient|MockObject $recipientMock */
        $recipientMock = $this->getMockBuilder(Recipient::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var RecipientsList|MockObject $recipientsListMock */
        $recipientsListMock = $this->getMockBuilder(RecipientsList::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['add'])
            ->getMock();
        $recipientsListMock->expects($this->once())->method('add')->willReturnSelf();
        d3GetOxidDIC()->set(RecipientsList::class, $recipientsListMock);

        /** @var MessageClient|MockObject $messageClientMock */
        $messageClientMock = $this->getMockBuilder(MessageClient::class)
            ->getMock();
        d3GetOxidDIC()->set(MessageClient::class, $messageClientMock);

        /** @var Sms|MockObject $sut */
        $sut = $this->getMockBuilder(Sms::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOrderRecipient'])
            ->getMock();
        $sut->method('getOrderRecipient')->willReturn($recipientMock);

        $this->assertSame(
            $recipientsListMock,
            $this->callMethod(
                $sut,
                'getOrderRecipientsList',
                [$orderMock]
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms::getOrderRecipient
     */
    public function canGetOrderRecipient()
    {
        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Recipient|MockObject $recipientMock */
        $recipientMock = $this->getMockBuilder(Recipient::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var OrderRecipients|MockObject $orderRecipientsMock */
        $orderRecipientsMock = $this->getMockBuilder(OrderRecipients::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSmsRecipient'])
            ->getMock();
        $orderRecipientsMock->method('getSmsRecipient')->willReturn($recipientMock);
        d3GetOxidDIC()->set(OrderRecipients::class, $orderRecipientsMock);

        /** @var Sms|MockObject $sut */
        $sut = $this->getMockBuilder(Sms::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertSame(
            $recipientMock,
            $this->callMethod(
                $sut,
                'getOrderRecipient',
                [$orderMock]
            )
        );
    }

    /**
     * @test
     * @param $sendSuccessful
     * @param $throwException
     * @param $expected
     * @return void
     * @throws ReflectionException
     * @dataProvider canSendCustomRecipientMessageDataProvider
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms::sendCustomRecipientMessage()
     */
    public function canSendCustomRecipientMessage($sendSuccessful, $throwException, $expected)
    {
        /** @var RecipientsList|MockObject $recipientsListMock */
        $recipientsListMock = $this->getMockBuilder(RecipientsList::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Response|MockObject $smsResponseMock */
        $smsResponseMock = $this->getMockBuilder(Response::class)
            ->onlyMethods(['isSuccessful'])
            ->disableOriginalConstructor()
            ->getMock();
        $smsResponseMock->method('isSuccessful')->willReturn($sendSuccessful);

        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockBuilder(Logger::class)
            ->onlyMethods(['warning'])
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock->expects($this->exactly((int) $throwException))->method('warning');

        /** @var UtilsView|MockObject $utilsViewMock */
        $utilsViewMock = $this->getMockBuilder(UtilsView::class)
            ->onlyMethods(['addErrorToDisplay'])
            ->getMock();
        $utilsViewMock->expects($this->exactly((int) $throwException))->method('addErrorToDisplay');
        d3GetOxidDIC()->set('d3ox.linkmobility.'.UtilsView::class, $utilsViewMock);

        /** @var Sms|MockObject $sut */
        $sut = $this->getMockBuilder(Sms::class)
            ->onlyMethods(['submitMessage', 'getLogger'])
            ->disableOriginalConstructor()
            ->getMock();
        $sut->method('submitMessage')->will(
            $throwException ?
                $this->throwException(oxNew(ApiException::class)) :
                $this->returnValue($smsResponseMock)
        );
        $sut->method('getLogger')->willReturn($loggerMock);

        $this->assertSame(
            $expected,
            $this->callMethod(
                $sut,
                'sendCustomRecipientMessage',
                [$recipientsListMock]
            )
        );
    }

    /**
     * @return array
     */
    public function canSendCustomRecipientMessageDataProvider(): array
    {
        return [
            'is successful'     => [true, false, true],
            'is not successful' => [false, false, false],
            'no recipient'      => [false, true, false],
        ];
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms::getRequest()
     */
    public function canGetRequest()
    {
        /** @var Client|MockObject $clientMock */
        $clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Configuration|MockObject $configurationMock */
        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->getMock();

        /** @var BinaryRequest|MockObject $binaryRequestMock */
        $binaryRequestMock = $this->getMockBuilder(BinaryRequest::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var RequestFactory|MockObject $requestFactoryMock */
        $requestFactoryMock = $this->getMockBuilder(RequestFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSmsRequest'])
            ->getMock();
        $requestFactoryMock->method('getSmsRequest')->willReturn($binaryRequestMock);
        d3GetOxidDIC()->set(RequestFactory::class, $requestFactoryMock);

        /** @var Sms|MockObject $sut */
        $sut = $this->getMockBuilder(Sms::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertSame(
            $binaryRequestMock,
            $this->callMethod(
                $sut,
                'getRequest',
                [$configurationMock, $clientMock]
            )
        );
    }

    /**
     * @test
     * @param $submitSuccessful
     * @return void
     * @throws ReflectionException
     * @dataProvider canSubmitMessageDataProvider
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms::submitMessage()
     */
    public function canSubmitMessage($submitSuccessful)
    {
        /** @var Recipient|MockObject $recipientMock */
        $recipientMock = $this->getMockBuilder(Recipient::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var RecipientsList|MockObject $recipientListMock */
        $recipientListMock = $this->getMockBuilder(RecipientsList::class)
            ->onlyMethods(['getRecipients'])
            ->disableOriginalConstructor()
            ->getMock();
        $recipientListMock->method('getRecipients')->willReturn([$recipientMock]);

        /** @var RecipientsList|MockObject $requestRecipientListMock */
        $requestRecipientListMock = $this->getMockBuilder(RecipientsList::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['add'])
            ->getMock();
        $requestRecipientListMock->expects($this->once())->method('add')->with(
            $this->identicalTo($recipientMock)
        );

        /** @var BinaryRequest|MockObject $binaryRequestMock */
        $binaryRequestMock = $this->getMockBuilder(BinaryRequest::class)
            ->onlyMethods(['getRecipientsList', 'getBody'])
            ->disableOriginalConstructor()
            ->getMock();
        $binaryRequestMock->method('getRecipientsList')->willReturn($requestRecipientListMock);
        $binaryRequestMock->method('getBody')->willReturn(['bodyFixture']);

        /** @var Response|MockObject $smsResponseMock */
        $smsResponseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isSuccessful', 'getErrorMessage'])
            ->getMock();
        $smsResponseMock->method('isSuccessful')->willReturn($submitSuccessful);
        $smsResponseMock->method('getErrorMessage')->willReturn('errorMessageFixture');

        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockBuilder(Logger::class)
            ->onlyMethods(['warning'])
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock->expects($this->exactly((int) !$submitSuccessful))->method('warning');

        /** @var Client|MockObject $clientMock */
        $clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request'])
            ->getMock();
        $clientMock->method('request')->willReturn($smsResponseMock);

        /** @var MessageClient|MockObject $messageClientMock */
        $messageClientMock = $this->getMockBuilder(MessageClient::class)
            ->onlyMethods(['getClient'])
            ->getMock();
        $messageClientMock->method('getClient')->willReturn($clientMock);
        d3GetOxidDIC()->set(MessageClient::class, $messageClientMock);

        /** @var Sms|MockObject $sut */
        $sut = $this->getMockBuilder(Sms::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequest', 'getLogger'])
            ->getMock();
        $sut->method('getRequest')->willReturn($binaryRequestMock);
        $sut->method('getLogger')->willReturn($loggerMock);

        $this->assertSame(
            $smsResponseMock,
            $this->callMethod(
                $sut,
                'submitMessage',
                [$recipientListMock]
            )
        );
    }

    /**
     * @return array
     */
    public function canSubmitMessageDataProvider(): array
    {
        return [
            'successful'    => [true],
            'not successful'=> [false]
        ];
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms::getTypeName
     */
    public function canGetTypeName()
    {
        /** @var Sms|MockObject $sut */
        $sut = $this->getMockBuilder(Sms::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMessage'])
            ->getMock();

        $this->assertIsString(
            $this->callMethod(
                $sut,
                'getTypeName'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms::getLogger()
     */
    public function canGetLogger()
    {
        /** @var Sms|MockObject $sut */
        $sut = $this->getMockBuilder(Sms::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMessage'])
            ->getMock();

        $this->assertInstanceOf(
            LoggerInterface::class,
            $this->callMethod(
                $sut,
                'getLogger'
            )
        );
    }
}