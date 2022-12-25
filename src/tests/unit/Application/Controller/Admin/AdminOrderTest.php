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

namespace D3\Linkmobility4OXID\tests\unit\Application\Controller\Admin;

use D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder;
use D3\Linkmobility4OXID\Application\Model\Exceptions\noRecipientFoundException;
use D3\Linkmobility4OXID\Application\Model\Exceptions\successfullySentException;
use D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms;
use D3\Linkmobility4OXID\Application\Model\OrderRecipients;
use D3\LinkmobilityClient\SMS\Response;
use D3\LinkmobilityClient\ValueObject\Recipient;
use D3\TestingTools\Development\CanAccessRestricted;
use InvalidArgumentException;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\TestingLibrary\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class AdminOrderTest extends UnitTestCase
{
    use CanAccessRestricted;

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder::__construct
     */
    public function canConstruct()
    {
        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->onlyMethods(['load'])
            ->getMock();
        $orderMock->method('load')->willReturn(true);

        /** @var Recipient|MockObject $recipientMock */
        $recipientMock = $this->getMockBuilder(Recipient::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var AdminOrder|MockObject $sut */
        $sut = $this->getMockBuilder(AdminOrder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['d3GetMockableOxNewObject', 'getEditObjectId', 'getRecipientFromCurrentOrder'])
            ->getMock();
        $sut->method('d3GetMockableOxNewObject')->willReturnCallback(
            function () use ($orderMock) {
                $args = func_get_args();
                switch ($args[0]) {
                    case Order::class:
                        return $orderMock;
                    default:
                        return call_user_func_array("oxNew", $args);
                }
            }
        );
        $sut->method('getEditObjectId')->willReturn('editObjId');
        $sut->method('getRecipientFromCurrentOrder')->willReturn($recipientMock);

        $this->callMethod(
            $sut,
            '__construct'
        );

        $this->assertSame(
            $orderMock,
            $this->getValue(
                $sut,
                'order'
            )
        );

        $this->assertSame(
            $recipientMock,
            $this->callMethod(
                $sut,
                'getViewDataElement',
                ['recipient']
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder::getRecipientFromCurrentOrder
     */
    public function canGetRecipientFromCurrentOrderPassed()
    {
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

        /** @var AdminOrder|MockObject $sut */
        $sut = $this->getMockBuilder(AdminOrder::class)
            ->onlyMethods(['d3GetMockableOxNewObject'])
            ->disableOriginalConstructor()
            ->getMock();
        $sut->method('d3GetMockableOxNewObject')->willReturnCallback(
            function () use ($orderRecipientsMock) {
                $args = func_get_args();
                switch ($args[0]) {
                    case OrderRecipients::class:
                        return $orderRecipientsMock;
                    default:
                        return call_user_func_array("oxNew", $args);
                }
            }
        );
        $this->setValue(
            $sut,
            'order',
            oxNew(Order::class)
        );

        $this->assertSame(
            $recipientMock,
            $this->callMethod(
                $sut,
                'getRecipientFromCurrentOrder'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder::getRecipientFromCurrentOrder
     */
    public function canGetRecipientFromCurrentOrderThrowsException()
    {
        /** @var UtilsView|MockObject $utilsViewMock */
        $utilsViewMock = $this->getMockBuilder(UtilsView::class)
            ->onlyMethods(['addErrorToDisplay'])
            ->getMock();
        $utilsViewMock->expects($this->once())->method('addErrorToDisplay');

        /** @var OrderRecipients|MockObject $orderRecipientsMock */
        $orderRecipientsMock = $this->getMockBuilder(OrderRecipients::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSmsRecipient'])
            ->getMock();
        $orderRecipientsMock->method('getSmsRecipient')->willThrowException(
            oxNew(noRecipientFoundException::class)
        );

        /** @var AdminOrder|MockObject $sut */
        $sut = $this->getMockBuilder(AdminOrder::class)
            ->onlyMethods(['d3GetMockableOxNewObject', 'd3GetMockableRegistryObject'])
            ->disableOriginalConstructor()
            ->getMock();
        $sut->method('d3GetMockableOxNewObject')->willReturnCallback(
            function () use ($orderRecipientsMock) {
                $args = func_get_args();
                switch ($args[0]) {
                    case OrderRecipients::class:
                        return $orderRecipientsMock;
                    default:
                        return call_user_func_array("oxNew", $args);
                }
            }
        );
        $sut->method('d3GetMockableRegistryObject')->willReturnCallback(
            function () use ($utilsViewMock) {
                $args = func_get_args();
                switch ($args[0]) {
                    case UtilsView::class:
                        return $utilsViewMock;
                    default:
                        return Registry::get($args[0]);
                }
            }
        );
        $this->setValue(
            $sut,
            'order',
            oxNew(Order::class)
        );

        $this->assertFalse(
            $this->callMethod(
                $sut,
                'getRecipientFromCurrentOrder'
            )
        );
    }

    /**
     * @test
     * @param $throwsException
     * @return void
     * @throws ReflectionException
     * @covers       \D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder::send
     * @dataProvider canSendDataProvider
     */
    public function canSend($throwsException)
    {
        /** @var UtilsView|MockObject $utilsViewMock */
        $utilsViewMock = $this->getMockBuilder(UtilsView::class)
            ->onlyMethods(['addErrorToDisplay'])
            ->getMock();
        $utilsViewMock->expects($this->once())->method('addErrorToDisplay');

        /** @var AdminOrder|MockObject $sut */
        $sut = $this->getMockBuilder(AdminOrder::class)
            ->onlyMethods(['d3GetMockableRegistryObject', 'sendMessage'])
            ->disableOriginalConstructor()
            ->getMock();
        $sut->method('d3GetMockableRegistryObject')->willReturnCallback(
            function () use ($utilsViewMock) {
                $args = func_get_args();
                switch ($args[0]) {
                    case UtilsView::class:
                        return $utilsViewMock;
                    default:
                        return Registry::get($args[0]);
                }
            }
        );
        $sut->method('sendMessage')->will(
            $throwsException ?
                $this->throwException(oxNew(noRecipientFoundException::class)) :
                $this->returnValue('successfully sent message')
        );

        $this->callMethod(
            $sut,
            'send'
        );
    }

    /**
     * @return array
     */
    public function canSendDataProvider(): array
    {
        return [
            'can send message'      => [false],
            'can not send message'  => [true],
        ];
    }

    /**
     * @param $message
     * @param $expectException
     * @param $expected
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder::getMessageBody
     * @dataProvider canGetMessageBodyDataProvider
     */
    public function canGetMessageBody($message, $expectException, $expected)
    {
        /** @var Request|MockObject $requestMock */
        $requestMock = $this->getMockBuilder(Request::class)
            ->onlyMethods(['getRequestEscapedParameter'])
            ->getMock();
        $requestMock->method('getRequestEscapedParameter')->willReturn($message);

        /** @var Request|MockObject $sut */
        $sut = $this->getMockBuilder(AdminOrder::class)
            ->onlyMethods(['d3GetMockableRegistryObject'])
            ->disableOriginalConstructor()
            ->getMock();
        $sut->method('d3GetMockableRegistryObject')->willReturnCallback(
            function () use ($requestMock) {
                $args = func_get_args();
                switch ($args[0]) {
                    case Request::class:
                        return $requestMock;
                    default:
                        return Registry::get($args[0]);
                }
            }
        );

        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $this->assertSame(
            $expected,
            $this->callMethod(
                $sut,
                'getMessageBody'
            )
        );
    }

    /**
     * @return array[]
     */
    public function canGetMessageBodyDataProvider(): array
    {
        return [
            'message not string'        => [[], true, ''],
            'message empty string'      => ['', true, ''],
            'message whitespace string' => ['  ', true, ''],
            'message right string'      => ['messagefixture', false, 'messagefixture'],
        ];
    }

    /**
     * @test
     * @param $canSendOrderMessage
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder::sendMessage
     * @dataProvider canSendMessageDataProvider
     */
    public function canSendMessage($canSendOrderMessage)
    {
        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->onlyMethods(['load'])
            ->getMock();
        $orderMock->method('load')->willReturn(true);

        /** @var Sms|MockObject $smsMock */
        $smsMock = $this->getMockBuilder(Sms::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sendOrderMessage'])
            ->getMock();
        $smsMock->expects($this->once())->method('sendOrderMessage')->willReturn($canSendOrderMessage);

        /** @var AdminOrder|MockObject $sut */
        $sut = $this->getMockBuilder(AdminOrder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['d3GetMockableOxNewObject', 'getMessageBody', 'getSuccessSentMessage', 'getUnsuccessfullySentMessage'])
            ->getMock();
        $sut->method('d3GetMockableOxNewObject')->willReturnCallback(
            function () use ($orderMock, $smsMock) {
                $args = func_get_args();
                switch ($args[0]) {
                    case Order::class:
                        return $orderMock;
                    case Sms::class:
                        return $smsMock;
                    default:
                        return call_user_func_array("oxNew", $args);
                }
            }
        );
        $sut->method('getMessageBody')->willReturn('messageBodyFixture');
        $sut->expects($this->exactly((int) $canSendOrderMessage))->method('getSuccessSentMessage')
            ->willReturn(oxNew(successfullySentException::class, 'expectedReturn'));
        $sut->expects($this->exactly((int) !$canSendOrderMessage))->method('getUnsuccessfullySentMessage')
            ->willReturn('expectedReturn');

        $this->assertIsString(
            $this->callMethod(
                $sut,
                'sendMessage'
            )
        );
    }

    /**
     * @return array
     */
    public function canSendMessageDataProvider(): array
    {
        return [
            'send order message'        => [true],
            'dont send order message'   => [false]
        ];
    }

    /**
     * @test
     * @param $hasResponse
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder::getSuccessSentMessage
     * @dataProvider canGetSuccessSentMessageDataProvider
     */
    public function canGetSuccessSentMessage($hasResponse)
    {
        /** @var successfullySentException|MockObject $successfullySendExceptionMock */
        $successfullySendExceptionMock = $this->getMockBuilder(successfullySentException::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Response|MockObject $resonseMock */
        $resonseMock = $this->getMockBuilder(Response::class)
            ->onlyMethods(['getSmsCount'])
            ->disableOriginalConstructor()
            ->getMock();
        $resonseMock->expects($hasResponse ? $this->once() : $this->never())->method('getSmsCount')
            ->willReturn(20);

        /** @var Sms|MockObject $smsMock */
        $smsMock = $this->getMockBuilder(Sms::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResponse'])
            ->getMock();
        $smsMock->method('getResponse')->willReturn($hasResponse ? $resonseMock : null);

        /** @var AdminOrder|MockObject $sut */
        $sut = $this->getMockBuilder(AdminOrder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['d3GetMockableOxNewObject'])
            ->getMock();
        $sut->method('d3GetMockableOxNewObject')->willReturnCallback(
            function () use ($successfullySendExceptionMock) {
                $args = func_get_args();
                switch ($args[0]) {
                    case successfullySentException::class:
                        return $successfullySendExceptionMock;
                    default:
                        return call_user_func_array("oxNew", $args);
                }
            }
        );

        $this->assertSame(
            $successfullySendExceptionMock,
            $this->callMethod(
                $sut,
                'getSuccessSentMessage',
                [$smsMock]
            )
        );
    }

    /**
     * @return array
     */
    public function canGetSuccessSentMessageDataProvider(): array
    {
        return [
            'has response'      => [true],
            'has no response'   => [false],
        ];
    }

    /**
     * @test
     * @param $hasResponse
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder::getUnsuccessfullySentMessage
     * @dataProvider canGetSuccessSentMessageDataProvider
     */
    public function canGetUnsuccessfullySentMessage($hasResponse)
    {
        /** @var Response|MockObject $resonseMock */
        $resonseMock = $this->getMockBuilder(Response::class)
            ->onlyMethods(['getErrorMessage'])
            ->disableOriginalConstructor()
            ->getMock();
        $resonseMock->expects($hasResponse ? $this->once() : $this->never())->method('getErrorMessage')
            ->willReturn('errorMessage');

        /** @var Sms|MockObject $smsMock */
        $smsMock = $this->getMockBuilder(Sms::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResponse'])
            ->getMock();
        $smsMock->method('getResponse')->willReturn($hasResponse ? $resonseMock : null);

        /** @var AdminOrder|MockObject $sut */
        $sut = $this->getMockBuilder(AdminOrder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertIsString(
            $this->callMethod(
                $sut,
                'getUnsuccessfullySentMessage',
                [$smsMock]
            )
        );
    }
}