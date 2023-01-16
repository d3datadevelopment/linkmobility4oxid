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
use D3\LinkmobilityClient\LoggerHandler;
use D3\TestingTools\Development\CanAccessRestricted;
use Monolog\Logger;
use OxidEsales\Eshop\Application\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class MessageSenderTest extends LMUnitTestCase
{
    use CanAccessRestricted;

    /**
     * @test
     *
     * @param $actionConfigured
     * @param $invocationCount
     * @param $throwException
     *
     * @return void
     * @throws ReflectionException
     * @dataProvider canSendOrderFinishedMessageDataProvider
     * @covers       \D3\Linkmobility4OXID\Application\Model\MessageSender::sendOrderFinishedMessage
     */
    public function canSendOrderFinishedMessage($actionConfigured, $invocationCount, $throwException)
    {
        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockBuilder(Logger::class)
            ->onlyMethods(['debug'])
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock->expects($this->exactly((int) $throwException))->method('debug');

        /** @var LoggerHandler|MockObject $loggerHandlerMock */
        $loggerHandlerMock = $this->getMockBuilder(LoggerHandler::class)
            ->onlyMethods(['getLogger'])
            ->getMock();
        $loggerHandlerMock->method('getLogger')->willReturn($loggerMock);
        d3GetOxidDIC()->set(LoggerHandler::class, $loggerHandlerMock);

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
        if ($throwException) {
            $sut->expects( $invocationCount )->method( 'sendMessageByOrder' )
                ->willThrowException( oxNew( noRecipientFoundException::class ) );
        } else {
            $sut->expects( $invocationCount )->method( 'sendMessageByOrder' );
        }

        $this->callMethod(
            $sut,
            'sendOrderFinishedMessage',
            [$orderMock, 'messageBody']
        );
    }

    /**
     * @test
     *
     * @param $actionConfigured
     * @param $invocationCount
     * @param $throwException
     *
     * @return void
     * @throws ReflectionException
     * @dataProvider canSendOrderFinishedMessageDataProvider
     * @covers       \D3\Linkmobility4OXID\Application\Model\MessageSender::sendSendedNowMessage
     */
    public function canSendSendedNowMessage($actionConfigured, $invocationCount, $throwException)
    {
        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockBuilder(Logger::class)
            ->onlyMethods(['debug'])
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock->expects($this->exactly((int) $throwException))->method('debug');

        /** @var LoggerHandler|MockObject $loggerHandlerMock */
        $loggerHandlerMock = $this->getMockBuilder(LoggerHandler::class)
            ->onlyMethods(['getLogger'])
            ->getMock();
        $loggerHandlerMock->method('getLogger')->willReturn($loggerMock);
        d3GetOxidDIC()->set(LoggerHandler::class, $loggerHandlerMock);

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
        if ($throwException) {
            $sut->expects( $invocationCount )->method( 'sendMessageByOrder' )
                ->willThrowException( oxNew( noRecipientFoundException::class ) );
        } else {
            $sut->expects( $invocationCount )->method( 'sendMessageByOrder' );
        }

        $this->callMethod(
            $sut,
            'sendSendedNowMessage',
            [$orderMock, 'messageBody']
        );
    }

    /**
     * @test
     *
     * @param $actionConfigured
     * @param $invocationCount
     * @param $throwException
     *
     * @return void
     * @throws ReflectionException
     * @dataProvider canSendOrderFinishedMessageDataProvider
     * @covers       \D3\Linkmobility4OXID\Application\Model\MessageSender::sendCancelOrderMessage
     */
    public function canSendCancelOrderMessage($actionConfigured, $invocationCount, $throwException)
    {
        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockBuilder(Logger::class)
            ->onlyMethods(['debug'])
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock->expects($this->exactly((int) $throwException))->method('debug');

        /** @var LoggerHandler|MockObject $loggerHandlerMock */
        $loggerHandlerMock = $this->getMockBuilder(LoggerHandler::class)
            ->onlyMethods(['getLogger'])
            ->getMock();
        $loggerHandlerMock->method('getLogger')->willReturn($loggerMock);
        d3GetOxidDIC()->set(LoggerHandler::class, $loggerHandlerMock);

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
        if ($throwException) {
            $sut->expects( $invocationCount )->method( 'sendMessageByOrder' )
                ->willThrowException( oxNew( noRecipientFoundException::class ) );
        } else {
            $sut->expects( $invocationCount )->method( 'sendMessageByOrder' );
        }

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
            'action configured, no exc'     => [true, $this->once(), false],
            'action configured, throw exc'  => [true, $this->once(), true],
            'action not configured'         => [false, $this->never(), false],
        ];
    }

    /**
     * @test
     * @param $messageBody
     * @param $invocationCount
     * @return void
     * @throws ReflectionException
     * @dataProvider canSendMessageByOrderDataProvider
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageSender::sendMessageByOrder
     */
    public function canSendMessageByOrder($messageBody, $invocationCount)
    {
        /** @var Sms|MockObject $smsMock */
        $smsMock = $this->getMockBuilder(Sms::class)
            ->onlyMethods(['sendOrderMessage'])
            ->disableOriginalConstructor()
            ->getMock();
        $smsMock->expects($invocationCount)->method('sendOrderMessage');

        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MessageSender|MockObject $sut */
        $sut = $this->getMockBuilder(MessageSender::class)
            ->onlyMethods(['getSms', 'getConfiguration'])
            ->getMock();
        $sut->method('getSms')->willReturn($smsMock);

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
            'has message body'      => ['body', $this->once()],
            'spaced message body'   => ['    ', $this->never()],
            'empty message body'    => ['', $this->never()],
        ];
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageSender::getSms
     */
    public function canGetSms()
    {
        /** @var MessageSender|MockObject $sut */
        $sut = $this->getMockBuilder(MessageSender::class)
            ->getMock();

        $this->assertInstanceOf(
            Sms::class,
            $this->callMethod(
                $sut,
                'getSms',
                ['messageFixture']
            )
        );
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
