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

use D3\DIContainerHandler\d3DicHandler;
use D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder;
use D3\Linkmobility4OXID\Application\Controller\Admin\AdminUser;
use D3\Linkmobility4OXID\Application\Model\Exceptions\noRecipientFoundException;
use D3\Linkmobility4OXID\Application\Model\Exceptions\successfullySentException;
use D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms;
use D3\Linkmobility4OXID\Application\Model\OrderRecipients;
use D3\Linkmobility4OXID\Application\Model\UserRecipients;
use D3\Linkmobility4OXID\tests\unit\LMUnitTestCase;
use D3\LinkmobilityClient\SMS\Response;
use D3\LinkmobilityClient\ValueObject\Recipient;
use D3\TestingTools\Development\CanAccessRestricted;
use InvalidArgumentException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsView;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class AdminSend extends LMUnitTestCase
{
    use CanAccessRestricted;

    protected $subjectUnderTestClass;
    protected $itemClass;
    protected $itemRecipientClass;

    /**
     * @return AdminOrder|MockObject
     * @throws ReflectionException
     */
    public function canConstruct()
    {
        /** @var Recipient|MockObject $recipientMock */
        $recipientMock = $this->getMockBuilder(Recipient::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var AdminOrder|MockObject $sut */
        $sut = $this->getMockBuilder($this->subjectUnderTestClass)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEditObjectId', 'getRecipientFromCurrentItem'])
            ->getMock();
        $sut->method('getEditObjectId')->willReturn('editObjId');
        $sut->method('getRecipientFromCurrentItem')->willReturn($recipientMock);

        $this->callMethod(
            $sut,
            '__construct'
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

        return $sut;
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
        d3GetOxidDIC()->set('d3ox.linkmobility.'.UtilsView::class, $utilsViewMock);

        /** @var OrderRecipients|MockObject $itemRecipientsMock */
        $itemRecipientsMock = $this->getMockBuilder($this->itemRecipientClass)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSmsRecipient'])
            ->getMock();
        $itemRecipientsMock->method('getSmsRecipient')->willThrowException(
            oxNew(noRecipientFoundException::class)
        );
        d3GetOxidDIC()->set(OrderRecipients::class, $itemRecipientsMock);
        d3GetOxidDIC()->set(UserRecipients::class, $itemRecipientsMock);

        /** @var AdminOrder|AdminUser|MockObject $sut */
        $sut = $this->getMockBuilder($this->subjectUnderTestClass)
            ->onlyMethods(['__construct'])
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
        d3GetOxidDIC()->set('d3ox.linkmobility.'.UtilsView::class, $utilsViewMock);

        /** @var AdminOrder|AdminUser|MockObject $sut */
        $sut = $this->getMockBuilder($this->subjectUnderTestClass)
            ->onlyMethods(['sendMessage'])
            ->disableOriginalConstructor()
            ->getMock();
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
        d3GetOxidDIC()->set('d3ox.linkmobility.'.Request::class, $requestMock);

        /** @var AdminOrder|AdminUser|MockObject $sut */
        $sut = $this->getMockBuilder($this->subjectUnderTestClass)
            ->onlyMethods(['__construct'])
            ->disableOriginalConstructor()
            ->getMock();

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
        d3GetOxidDIC()->set(successfullySentException::class, $successfullySendExceptionMock);

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
            ->onlyMethods(['__construct'])
            ->getMock();

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