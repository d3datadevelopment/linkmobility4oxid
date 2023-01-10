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

namespace D3\Linkmobility4OXID\tests\unit\Modules\Core;

use D3\Linkmobility4OXID\Application\Model\MessageSender;
use D3\Linkmobility4OXID\Modules\Application\Model\OrderModel;
use D3\Linkmobility4OXID\Modules\Core\EmailCore;
use D3\Linkmobility4OXID\tests\unit\LMUnitTestCase;
use D3\TestingTools\Development\CanAccessRestricted;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Email;
use OxidEsales\EshopCommunity\Internal\Framework\Templating\TemplateRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class EmailCoreTest extends LMUnitTestCase
{
    use CanAccessRestricted;

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Modules\Core\EmailCore::sendOrderEmailToUser
     */
    public function canSendOrderEmailToUser()
    {
        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var EmailCore|MockObject $sut */
        $sut = $this->getMockBuilder(Email::class)
            ->onlyMethods(['d3CallMockableFunction', 'd3SendOrderFinishedMessageToUser'])
            ->getMock();
        $sut->method('d3CallMockableFunction')->willReturn('returnFixture');
        $sut->expects($this->once())->method('d3SendOrderFinishedMessageToUser')->with(
            $this->identicalTo($orderMock)
        );

        $this->assertSame(
            'returnFixture',
            $this->callMethod(
                $sut,
                'sendOrderEmailToUser',
                [$orderMock]
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Modules\Core\EmailCore::sendSendedNowMail
     */
    public function canSendSendedNowMail()
    {
        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var EmailCore|MockObject $sut */
        $sut = $this->getMockBuilder(Email::class)
            ->onlyMethods(['d3CallMockableFunction', 'd3SendedNowMessage'])
            ->getMock();
        $sut->method('d3CallMockableFunction')->willReturn('returnFixture');
        $sut->expects($this->once())->method('d3SendedNowMessage')->with(
            $this->identicalTo($orderMock)
        );

        $this->assertSame(
            'returnFixture',
            $this->callMethod(
                $sut,
                'sendSendedNowMail',
                [$orderMock]
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Modules\Core\EmailCore::d3SendOrderFinishedMessageToUser
     */
    public function canSendOrderFinishedMessageToUser()
    {
        /** @var OrderModel|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MessageSender|MockObject $messageSenderMock */
        $messageSenderMock = $this->getMockBuilder(MessageSender::class)
            ->onlyMethods(['sendOrderFinishedMessage'])
            ->getMock();
        $messageSenderMock->expects($this->once())->method('sendOrderFinishedMessage')->with(
            $this->identicalTo($orderMock)
        );
        d3GetOxidDIC()->set(MessageSender::class, $messageSenderMock);

        /** @var EmailCore|MockObject $sut */
        $sut = $this->getMockBuilder(Email::class)
            ->onlyMethods(['d3GetOrderFinishedSmsMessageBody'])
            ->getMock();
        $sut->method('d3GetOrderFinishedSmsMessageBody')->with(
            $this->identicalTo($orderMock)
        )->willReturn('smsMessageBodyFixture');

        $this->callMethod(
            $sut,
            'd3SendOrderFinishedMessageToUser',
            [$orderMock]
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Modules\Core\EmailCore::d3GetOrderFinishedSmsMessageBody
     */
    public function canGetOrderFinishedSmsMessageBody()
    {
        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var TemplateRenderer|MockObject $tplRendererMock */
        $tplRendererMock = $this->getMockBuilder(TemplateRenderer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['renderTemplate'])
            ->getMock();
        $tplRendererMock->method('renderTemplate')->willReturn('renderedFixture');

        /** @var EmailCore|MockObject $sut */
        $sut = $this->getMockBuilder(Email::class)
            ->onlyMethods(['d3GetTplRenderer', 'setViewData', 'getViewData'])
            ->getMock();
        $sut->method('d3GetTplRenderer')->willReturn($tplRendererMock);
        $sut->method('setViewData')->with(
            $this->identicalTo('order'),
            $this->identicalTo($orderMock)
        );
        $sut->method('getViewData')->willReturn([]);

        $this->assertSame(
            'renderedFixture',
            $this->callMethod(
                $sut,
                'd3GetOrderFinishedSmsMessageBody',
                [$orderMock]
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Modules\Core\EmailCore::d3SendedNowMessage
     */
    public function canSendedNowMessage()
    {
        /** @var OrderModel|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MessageSender|MockObject $messageSenderMock */
        $messageSenderMock = $this->getMockBuilder(MessageSender::class)
            ->onlyMethods(['sendSendedNowMessage'])
            ->getMock();
        $messageSenderMock->expects($this->once())->method('sendSendedNowMessage')->with(
            $this->identicalTo($orderMock)
        );
        d3GetOxidDIC()->set(MessageSender::class, $messageSenderMock);

        /** @var EmailCore|MockObject $sut */
        $sut = $this->getMockBuilder(Email::class)
            ->onlyMethods(['d3GetSendedNowSmsMessageBody'])
            ->getMock();
        $sut->method('d3GetSendedNowSmsMessageBody')->with(
            $this->identicalTo($orderMock)
        )->willReturn('smsMessageBodyFixture');

        $this->callMethod(
            $sut,
            'd3SendedNowMessage',
            [$orderMock]
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Modules\Core\EmailCore::d3GetSendedNowSmsMessageBody
     */
    public function canGetSendedNowSmsMessageBody()
    {
        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var TemplateRenderer|MockObject $tplRendererMock */
        $tplRendererMock = $this->getMockBuilder(TemplateRenderer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['renderTemplate'])
            ->getMock();
        $tplRendererMock->method('renderTemplate')->willReturn('renderedFixture');

        /** @var EmailCore|MockObject $sut */
        $sut = $this->getMockBuilder(Email::class)
            ->onlyMethods(['d3GetTplRenderer', 'setViewData', 'getViewData'])
            ->getMock();
        $sut->method('d3GetTplRenderer')->willReturn($tplRendererMock);
        $sut->method('setViewData')->with(
            $this->identicalTo('order'),
            $this->identicalTo($orderMock)
        );
        $sut->method('getViewData')->willReturn([]);

        $this->assertSame(
            'renderedFixture',
            $this->callMethod(
                $sut,
                'd3GetSendedNowSmsMessageBody',
                [$orderMock]
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Modules\Core\EmailCore::d3SendCancelMessage
     */
    public function canSendCancelMessage()
    {
        /** @var OrderModel|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MessageSender|MockObject $messageSenderMock */
        $messageSenderMock = $this->getMockBuilder(MessageSender::class)
            ->onlyMethods(['sendCancelOrderMessage'])
            ->getMock();
        $messageSenderMock->expects($this->once())->method('sendCancelOrderMessage')->with(
            $this->identicalTo($orderMock)
        );
        d3GetOxidDIC()->set(MessageSender::class, $messageSenderMock);

        /** @var EmailCore|MockObject $sut */
        $sut = $this->getMockBuilder(Email::class)
            ->onlyMethods(['d3GetCancelOrderSmsMessageBody'])
            ->getMock();
        $sut->method('d3GetCancelOrderSmsMessageBody')->with(
            $this->identicalTo($orderMock)
        )->willReturn('smsMessageBodyFixture');

        $this->callMethod(
            $sut,
            'd3SendCancelMessage',
            [$orderMock]
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Modules\Core\EmailCore::d3GetCancelOrderSmsMessageBody
     */
    public function canGetCancelOrderSmsMessageBody()
    {
        /** @var Order|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var TemplateRenderer|MockObject $tplRendererMock */
        $tplRendererMock = $this->getMockBuilder(TemplateRenderer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['renderTemplate'])
            ->getMock();
        $tplRendererMock->method('renderTemplate')->willReturn('renderedFixture');

        /** @var EmailCore|MockObject $sut */
        $sut = $this->getMockBuilder(Email::class)
            ->onlyMethods(['d3GetTplRenderer', 'setViewData', 'getViewData'])
            ->getMock();
        $sut->method('d3GetTplRenderer')->willReturn($tplRendererMock);
        $sut->method('setViewData')->with(
            $this->identicalTo('order'),
            $this->identicalTo($orderMock)
        );
        $sut->method('getViewData')->willReturn([]);

        $this->assertSame(
            'renderedFixture',
            $this->callMethod(
                $sut,
                'd3GetCancelOrderSmsMessageBody',
                [$orderMock]
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Modules\Core\EmailCore::d3GetTplRenderer
     */
    public function canGetTplRenderer()
    {
        /** @var EmailCore|MockObject $sut */
        $sut = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(
            TemplateRenderer::class,
            $this->callMethod(
                $sut,
                'd3GetTplRenderer'
            )
        );
    }
}
