<?php

/**
 * This Software is the property of Data Development and is protected
 * by copyright law - it is NOT Freeware.
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 * http://www.shopmodule.com
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author        D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link          http://www.oxidmodule.com
 */

namespace D3\Linkmobility4OXID\Modules\Application\Controller;

use D3\Linkmobility4OXID\Application\Model\Sms;
use D3\Linkmobility4OXID\Modules\Core\EmailCore;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Email;

class StartController extends StartController_parent
{
    public function render()
    {
        $message = "testMessagetestMessagetestMessagetestMessagetestMessagetestMessage";
        //$message = "test\tMessage\ttest\tMessage";

        $order = oxNew(Order::class);
        $order->load('eda00356201d7ec2bcf31166fa6c37dd');


        /** @var EmailCore $mail */
        $mail = oxNew(Email::class);
        $mail->d3SendOrderFinishedMessageToUser($order);

        return parent::render();
    }
}