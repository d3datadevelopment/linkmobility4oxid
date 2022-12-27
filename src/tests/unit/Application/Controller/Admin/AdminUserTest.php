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

use D3\Linkmobility4OXID\Application\Controller\Admin\AdminUser;
use D3\Linkmobility4OXID\Application\Model\Exceptions\successfullySentException;
use D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms;
use D3\Linkmobility4OXID\Application\Model\UserRecipients;
use D3\TestingTools\Development\CanAccessRestricted;
use OxidEsales\Eshop\Application\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class AdminUserTest extends AdminSend
{
    use CanAccessRestricted;

    protected $subjectUnderTestClass = AdminUser::class;
    protected $itemClass = User::class;
    protected $itemRecipientClass = UserRecipients::class;

    /**
     * @test
     * @param $canSendItemMessage
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder::sendMessage
     * @dataProvider canSendMessageDataProvider
     */
    public function canSendMessage($canSendItemMessage)
    {
        /** @var Sms|MockObject $smsMock */
        $smsMock = $this->getMockBuilder(Sms::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sendUserAccountMessage'])
            ->getMock();
        $smsMock->expects($this->once())->method('sendUserAccountMessage')->willReturn($canSendItemMessage);

        /** @var AdminUser|MockObject $sut */
        $sut = $this->getMockBuilder(AdminUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['d3GetMockableOxNewObject', 'getMessageBody', 'getSuccessSentMessage', 'getUnsuccessfullySentMessage'])
            ->getMock();
        $sut->method('d3GetMockableOxNewObject')->willReturnCallback(
            function () use ($smsMock) {
                $args = func_get_args();
                switch ($args[0]) {
                    case Sms::class:
                        return $smsMock;
                    default:
                        return call_user_func_array("oxNew", $args);
                }
            }
        );
        $sut->method('getMessageBody')->willReturn('messageBodyFixture');
        $sut->expects($this->exactly((int) $canSendItemMessage))->method('getSuccessSentMessage')
            ->willReturn(oxNew(successfullySentException::class, 'expectedReturn'));
        $sut->expects($this->exactly((int) !$canSendItemMessage))->method('getUnsuccessfullySentMessage')
            ->willReturn('expectedReturn');

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
}