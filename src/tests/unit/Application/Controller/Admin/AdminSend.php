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
use D3\Linkmobility4OXID\Application\Controller\Admin\AdminUser;
use D3\Linkmobility4OXID\Application\Model\Exceptions\noRecipientFoundException;
use D3\Linkmobility4OXID\Application\Model\Exceptions\successfullySentException;
use D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms;
use D3\Linkmobility4OXID\Application\Model\OrderRecipients;
use D3\Linkmobility4OXID\Application\Model\UserRecipients;
use D3\LinkmobilityClient\SMS\Response;
use D3\LinkmobilityClient\ValueObject\Recipient;
use D3\TestingTools\Development\CanAccessRestricted;
use InvalidArgumentException;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\TestingLibrary\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class AdminSend extends UnitTestCase
{
    use CanAccessRestricted;

    protected $subjectUnderTestClass;
    protected $itemClass;
    protected $itemRecipientClass;

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder::__construct
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminUser::__construct
     */
    public function canConstruct()
    {
        /** @var Order|MockObject $itemMock */
        $itemMock = $this->getMockBuilder($this->itemClass)
            ->onlyMethods(['load'])
            ->getMock();
        $itemMock->method('load')->willReturn(true);

        /** @var Recipient|MockObject $recipientMock */
        $recipientMock = $this->getMockBuilder(Recipient::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var AdminOrder|MockObject $sut */
        $sut = $this->getMockBuilder($this->subjectUnderTestClass)
            ->disableOriginalConstructor()
            ->onlyMethods(['d3GetMockableOxNewObject', 'getEditObjectId', 'getRecipientFromCurrentItem'])
            ->getMock();
        $sut->method('d3GetMockableOxNewObject')->willReturnCallback(
            function () use ($itemMock) {
                $args = func_get_args();
                switch ($args[0]) {
                    case Order::class:
                        return $itemMock;
                    default:
                        return call_user_func_array("oxNew", $args);
                }
            }
        );
        $sut->method('getEditObjectId')->willReturn('editObjId');
        $sut->method('getRecipientFromCurrentItem')->willReturn($recipientMock);

        $this->callMethod(
            $sut,
            '__construct'
        );

        $this->assertSame(
            $itemMock,
            $this->getValue(
                $sut,
                'item'
            )
        );

        $this->assertInstanceOf(
            $this->itemRecipientClass,
            $this->getValue(
                $sut,
                'itemRecipients'
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
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder::getRecipientFromCurrentItem
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminUser::getRecipientFromCurrentItem
     */
    public function canGetRecipientFromCurrentItemPassed()
    {
        /** @var Recipient|MockObject $recipientMock */
        $recipientMock = $this->getMockBuilder(Recipient::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var OrderRecipients|UserRecipients|MockObject $itemsRecipientsMock */
        $itemsRecipientsMock = $this->getMockBuilder($this->itemRecipientClass)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSmsRecipient'])
            ->getMock();
        $itemsRecipientsMock->method('getSmsRecipient')->willReturn($recipientMock);

        /** @var AdminOrder|AdminUser|MockObject $sut */
        $sut = $this->getMockBuilder($this->subjectUnderTestClass)
            ->onlyMethods(['send'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->setValue(
            $sut,
            'item',
            oxNew($this->itemClass)
        );

        $this->setValue(
            $sut,
            'itemRecipients',
            $itemsRecipientsMock
        );

        $this->assertSame(
            $recipientMock,
            $this->callMethod(
                $sut,
                'getRecipientFromCurrentItem'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder::getRecipientFromCurrentItem
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminUser::getRecipientFromCurrentItem
     */
    public function canGetRecipientFromCurrentItemThrowsException()
    {
        /** @var UtilsView|MockObject $utilsViewMock */
        $utilsViewMock = $this->getMockBuilder(UtilsView::class)
            ->onlyMethods(['addErrorToDisplay'])
            ->getMock();
        $utilsViewMock->expects($this->once())->method('addErrorToDisplay');
        /** @var OrderRecipients|MockObject $itemRecipientsMock */
        $itemRecipientsMock = $this->getMockBuilder($this->itemRecipientClass)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSmsRecipient'])
            ->getMock();
        $itemRecipientsMock->method('getSmsRecipient')->willThrowException(
            oxNew(noRecipientFoundException::class)
        );

        /** @var AdminOrder|AdminUser|MockObject $sut */
        $sut = $this->getMockBuilder($this->subjectUnderTestClass)
            ->onlyMethods(['d3GetMockableOxNewObject', 'd3GetMockableRegistryObject'])
            ->disableOriginalConstructor()
            ->getMock();
        $sut->method('d3GetMockableOxNewObject')->willReturnCallback(
            function () use ($itemRecipientsMock) {
                $args = func_get_args();
                switch ($args[0]) {
                    case OrderRecipients::class:
                    case UserRecipients::class:
                        return $itemRecipientsMock;
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
            'item',
            oxNew($this->itemClass)
        );
        $this->setValue(
            $sut,
            'itemRecipients',
            oxNew($this->itemRecipientClass, oxNew($this->itemClass))
        );

        $this->assertFalse(
            $this->callMethod(
                $sut,
                'getRecipientFromCurrentItem'
            )
        );
    }

    /**
     * @test
     * @param $throwsException
     * @return void
     * @throws ReflectionException
     * @covers       \D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder::send
     * @covers       \D3\Linkmobility4OXID\Application\Controller\Admin\AdminUser::send
     * @dataProvider canSendDataProvider
     */
    public function canSend($throwsException)
    {
        /** @var UtilsView|MockObject $utilsViewMock */
        $utilsViewMock = $this->getMockBuilder(UtilsView::class)
            ->onlyMethods(['addErrorToDisplay'])
            ->getMock();
        $utilsViewMock->expects($this->once())->method('addErrorToDisplay');

        /** @var AdminOrder|AdminUser|MockObject $sut */
        $sut = $this->getMockBuilder($this->subjectUnderTestClass)
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
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminUser::getMessageBody
     * @dataProvider canGetMessageBodyDataProvider
     */
    public function canGetMessageBody($message, $expectException, $expected)
    {
        /** @var Request|MockObject $requestMock */
        $requestMock = $this->getMockBuilder(Request::class)
            ->onlyMethods(['getRequestEscapedParameter'])
            ->getMock();
        $requestMock->method('getRequestEscapedParameter')->willReturn($message);

        /** @var AdminOrder|AdminUser|MockObject $sut */
        $sut = $this->getMockBuilder($this->subjectUnderTestClass)
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
     * @return array
     */
    public function canSendMessageDataProvider(): array
    {
        return [
            'send item message'        => [true],
            'dont send item message'   => [false]
        ];
    }

    /**
     * @test
     * @param $hasResponse
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder::getSuccessSentMessage
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminUser::getSuccessSentMessage
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

        /** @var AdminOrder|AdminUser|MockObject $sut */
        $sut = $this->getMockBuilder($this->subjectUnderTestClass)
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
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminUser::getUnsuccessfullySentMessage
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

        /** @var AdminOrder|AdminUser|MockObject $sut */
        $sut = $this->getMockBuilder($this->subjectUnderTestClass)
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