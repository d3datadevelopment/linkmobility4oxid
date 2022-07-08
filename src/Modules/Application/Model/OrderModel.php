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

namespace D3\Linkmobility4OXID\Modules\Application\Model;

use D3\Linkmobility4OXID\Application\Model\MessageSender;
use OxidEsales\Eshop\Core\Email;

class OrderModel extends OrderModel_parent
{
    public function cancelOrder()
    {
        parent::cancelOrder();

        if ($this->getFieldData('oxstorno') === 1) {
            $Email = oxNew( Email::class );
            $Email->d3SendCancelMessage($this);
        }
    }
}