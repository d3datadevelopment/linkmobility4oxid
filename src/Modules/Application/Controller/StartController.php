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
use OxidEsales\Eshop\Application\Model\User;

class StartController extends StartController_parent
{
    public function render()
    {
        $message = "testMessagetestMessagetestMessagetestMessagetestMessagetestMessage";
        //$message = "test\tMessage\ttest\tMessage";

        $user = oxNew(User::class);
        $user->load('oxdefaultadmin');
        $success = oxNew(Sms::class)->sendUserAccountMessage($user, $message);
dumpvar($success);
        return parent::render();
    }
}