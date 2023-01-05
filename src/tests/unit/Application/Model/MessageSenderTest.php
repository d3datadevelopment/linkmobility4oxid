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
use D3\Linkmobility4OXID\Application\Model\MessageSender;
use D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms;
use D3\Linkmobility4OXID\tests\unit\LMUnitTestCase;
use D3\TestingTools\Development\CanAccessRestricted;
use OxidEsales\Eshop\Application\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class MessageSenderTest extends LMUnitTestCase
{
    use CanAccessRestricted;

    /**
     * @test
     * @param $actionConfigured
     * @param $invocationCount
     * @return void
     * @throws ReflectionException
     * @dataProvider canSendOrderFinishedMessageDataProvider
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageSender::sendOrderFinishedMessage
     */
    public function canSendOrderFinishedMessage($actionConfigured, $invocationCount)
    {
        /** @var Configuration|MockObject $configurationMock */
        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['sendOrderFinishedMessage'])
            ->getMock();
        $configurationMock->method('sendOrderFinishedMessage')->willReturn($actionConfigured);

        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MessageSender|MockObject $sut */
        $sut = $this->getMockBuilder(MessageSender::class)
            ->onlyMethods(['getConfiguration', 'sendMessageByOrder'])
            ->getMock();
        $sut->method('getConfiguration')->willReturn($configurationMock);
        $sut->expects($invocationCount)->method('sendMessageByOrder');

        $this->callMethod(
            $sut,
            'sendOrderFinishedMessage',
            [$orderMock, 'messageBody']
        );
    }

    /**
     * @test
     * @param $actionConfigured
     * @param $invocationCount
     * @return void
     * @throws ReflectionException
     * @dataProvider canSendOrderFinishedMessageDataProvider
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageSender::sendSendedNowMessage
     */
    public function canSendSendedNowMessage($actionConfigured, $invocationCount)
    {
        /** @var Configuration|MockObject $configurationMock */
        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['sendOrderSendedNowMessage'])
            ->getMock();
        $configurationMock->method('sendOrderSendedNowMessage')->willReturn($actionConfigured);

        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MessageSender|MockObject $sut */
        $sut = $this->getMockBuilder(MessageSender::class)
            ->onlyMethods(['getConfiguration', 'sendMessageByOrder'])
            ->getMock();
        $sut->method('getConfiguration')->willReturn($configurationMock);
        $sut->expects($invocationCount)->method('sendMessageByOrder');

        $this->callMethod(
            $sut,
            'sendSendedNowMessage',
            [$orderMock, 'messageBody']
        );
    }

    /**
     * @test
     * @param $actionConfigured
     * @param $invocationCount
     * @return void
     * @throws ReflectionException
     * @dataProvider canSendOrderFinishedMessageDataProvider
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageSender::sendCancelOrderMessage
     */
    public function canSendCancelOrderMessage($actionConfigured, $invocationCount)
    {
        /** @var Configuration|MockObject $configurationMock */
        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['sendOrderCanceledMessage'])
            ->getMock();
        $configurationMock->method('sendOrderCanceledMessage')->willReturn($actionConfigured);

        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MessageSender|MockObject $sut */
        $sut = $this->getMockBuilder(MessageSender::class)
            ->onlyMethods(['getConfiguration', 'sendMessageByOrder'])
            ->getMock();
        $sut->method('getConfiguration')->willReturn($configurationMock);
        $sut->expects($invocationCount)->method('sendMessageByOrder');

        $this->callMethod(
            $sut,
            'sendCancelOrderMessage',
            [$orderMock, 'messageBody']
        );
    }

    /**
     * @return array[]
     */
    public function canSendOrderFinishedMessageDataProvider(): array
    {
        return [
            'action configured'     => [true, $this->once()],
            'action not configured' => [false, $this->never()],
        ];
    }

    /**
     * @test
     * @param $messageBody
     * @param $invocationCount
     * @param $throwException
     * @return void
     * @throws ReflectionException
     * @dataProvider canSendMessageByOrderDataProvider
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageSender::sendMessageByOrder
     */
    public function canSendMessageByOrder($messageBody, $invocationCount, $throwException)
    {
        /** @var Sms|MockObject $smsMock */
        $smsMock = $this->getMockBuilder(Sms::class)
            ->onlyMethods(['sendOrderMessage'])
            ->disableOriginalConstructor()
            ->getMock();
        $smsMock->expects($invocationCount)->method('sendOrderMessage')->will(
            $throwException ?
                $this->throwException(d3GetOxidDIC()->get(noRecipientFoundException::class)):
                $this->returnValue(true)
        );
        d3GetOxidDIC()->set(Sms::class, $smsMock);

        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MessageSender|MockObject $sut */
        $sut = $this->getMockBuilder(MessageSender::class)
            ->onlyMethods(['getConfiguration'])
            ->getMock();

        $this->callMethod(
            $sut,
            'sendMessageByOrder',
            [$orderMock, $messageBody]
        );
    }

    /**
     * @return array
     */
    public function canSendMessageByOrderDataProvider(): array
    {
        return [
            'has message body no exc'   => ['body', $this->once(), false],
            'has message body throw exc'=> ['body', $this->once(), true],
            'spaced message body'   => ['    ', $this->never(), false],
            'empty message body'    => ['', $this->never(), false],
        ];
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageSender::getConfiguration
     */
    public function canGetConfiguration()
    {
        /** @var MessageSender|MockObject $sut */
        $sut = $this->getMockBuilder(MessageSender::class)
            ->onlyMethods(['sendMessageByOrder'])
            ->getMock();

        $this->assertInstanceOf(
            Configuration::class,
            $this->callMethod(
                $sut,
                'getConfiguration'
            )
        );
    }
}