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
use D3\LinkmobilityClient\LoggerHandler;
use D3\TestingTools\Development\CanAccessRestricted;
use Monolog\Logger;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\UtilsView;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class AdminOrderTest extends AdminSend
{
    use CanAccessRestricted;

    protected $subjectUnderTestClass = AdminOrder::class;
    protected $itemClass = Order::class;
    protected $itemRecipientClass = OrderRecipients::class;

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder::__construct
     */
    public function canConstruct()
    {
        /** @var Order|MockObject $itemMock */
        $itemMock = $this->getMockBuilder($this->itemClass)
            ->onlyMethods(['load'])
            ->getMock();
        $itemMock->method('load')->willReturn(true);
        d3GetOxidDIC()->set('d3ox.linkmobility.'.Order::class, $itemMock);

        $sut = parent::canConstruct();

        $this->assertSame(
            $itemMock,
            $this->getValue(
                $sut,
                'item'
            )
        );
    }

    /**
     * @test
     *
     * @param $canSendItemMessage
     * @param $throwException
     *
     * @return void
     * @throws ReflectionException
     * @covers       \D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder::sendMessage
     * @dataProvider canSendMessageDataProvider
     */
    public function canSendMessage($canSendItemMessage, $throwException)
    {
        /** @var Sms|MockObject $smsMock */
        $smsMock = $this->getMockBuilder(Sms::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sendOrderMessage'])
            ->getMock();
        $smsMock->expects($this->once())->method('sendOrderMessage')->will(
            $throwException ?
                    $this->throwException(oxNew(noRecipientFoundException::class)) :
                    $this->returnValue($canSendItemMessage)
        );

        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockBuilder(Logger::class)
            ->onlyMethods(['warning'])
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock->expects($this->exactly((int) $throwException))->method('warning');

        /** @var LoggerHandler|MockObject $loggerHandlerMock */
        $loggerHandlerMock = $this->getMockBuilder(LoggerHandler::class)
                                  ->onlyMethods(['getLogger'])
                                  ->getMock();
        $loggerHandlerMock->method('getLogger')->willReturn($loggerMock);
        d3GetOxidDIC()->set(LoggerHandler::class, $loggerHandlerMock);

        /** @var UtilsView|MockObject $utilsViewMock */
        $utilsViewMock = $this->getMockBuilder(UtilsView::class)
            ->onlyMethods(['addErrorToDisplay'])
            ->getMock();
        $utilsViewMock->expects($this->exactly((int) $throwException))->method('addErrorToDisplay');
        d3GetOxidDIC()->set('d3ox.linkmobility.'.UtilsView::class, $utilsViewMock);

        /** @var AdminOrder|MockObject $sut */
        $sut = $this->getMockBuilder(AdminOrder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMessageBody', 'getSuccessSentMessage', 'getUnsuccessfullySentMessage', 'getSms'])
            ->getMock();
        $sut->method('getMessageBody')->willReturn('messageBodyFixture');
        $sut->expects($this->exactly((int) $canSendItemMessage))->method('getSuccessSentMessage')
            ->willReturn(oxNew(successfullySentException::class, 'expectedReturn'));
        $sut->expects($this->exactly((int) (!$canSendItemMessage && !$throwException)))->method('getUnsuccessfullySentMessage')
            ->willReturn('expectedReturn');
        $sut->method('getSms')->willReturn($smsMock);

        $this->setValue(
            $sut,
            'item',
            oxNew($this->itemClass)
        );

        $this->assertIsString(
            $this->callMethod(
                $sut,
                'sendMessage'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder::getSms
     */
    public function canGetSms()
    {
        /** @var AdminOrder|MockObject $sut */
        $sut = $this->getMockBuilder(AdminOrder::class)
            ->disableOriginalConstructor()
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
}
