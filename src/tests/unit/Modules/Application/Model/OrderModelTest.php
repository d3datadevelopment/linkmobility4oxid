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

namespace D3\Linkmobility4OXID\tests\unit\Modules\Application\Model;

use D3\Linkmobility4OXID\Modules\Application\Model\OrderModel;
use D3\Linkmobility4OXID\Modules\Core\EmailCore;
use D3\Linkmobility4OXID\tests\unit\LMUnitTestCase;
use D3\TestingTools\Development\CanAccessRestricted;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Email;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class OrderModelTest extends LMUnitTestCase
{
    use CanAccessRestricted;

    /**
     * @test
     * @param $stornoValue
     * @param $invocationCount
     * @return void
     * @throws ReflectionException
     * @dataProvider canCancelOrderDataProvider
     * @covers \D3\Linkmobility4OXID\Modules\Application\Model\OrderModel::cancelOrder
     */
    public function canCancelOrder($stornoValue, $invocationCount)
    {
        /** @var EmailCore|MockObject $EmailMock */
        $EmailMock = $this->getMockBuilder(Email::class)
            ->onlyMethods(['d3SendCancelMessage'])
            ->getMock();
        $EmailMock->expects($invocationCount)->method('d3SendCancelMessage');
        d3GetOxidDIC()->set('d3ox.linkmobility.'.Email::class, $EmailMock);

        /** @var OrderModel|MockObject $sut */
        $sut = $this->getMockBuilder(Order::class)
            ->onlyMethods(['getFieldData', 'd3CallMockableFunction'])
            ->getMock();
        $sut->method('getFieldData')->willReturnCallback(
            function () use ($stornoValue) {
                $aArgs = func_get_args();

                switch ($aArgs[0]) {
                    case 'oxstorno':
                        return $stornoValue;
                }

                $this->fail('Unknown variable '.$aArgs[0]);
            }
        );
        $sut->method('d3CallMockableFunction')->willReturn(true);

        $this->callMethod(
            $sut,
            'cancelOrder'
        );
    }

    /**
     * @return array[]
     */
    public function canCancelOrderDataProvider(): array
    {
        return [
            'is canceled'   => [true, $this->once()],
            'not canceled'  => [false, $this->never()],
        ];
    }
}