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

namespace D3\Linkmobility4OXID\Application\Controller\Admin;

use D3\Linkmobility4OXID\Application\Model\Exceptions\noRecipientFoundException;
use D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms;
use D3\Linkmobility4OXID\Application\Model\UserRecipients;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;

class AdminUser extends AdminSendController
{
    protected $_sThisTemplate = 'd3adminuser.tpl';

    /**
     * @var Sms
     */
    protected $sms;

    /** @var User */
    protected $item;

    /** @var UserRecipients */
    protected $itemRecipients;

    public function __construct()
    {
        $this->item = $this->d3GetMockableOxNewObject(User::class);
        $this->itemRecipients = $this->d3GetMockableOxNewObject(UserRecipients::class, $this->item);
        parent::__construct();
    }

    /**
     * @return string
     * @throws noRecipientFoundException
     */
    protected function sendMessage(): string
    {
        /** @var Sms $sms */
        $sms = $this->d3GetMockableOxNewObject(Sms::class, $this->getMessageBody());
        return $sms->sendUserAccountMessage($this->item) ?
            (string) $this->getSuccessSentMessage($sms) :
            $this->getUnsuccessfullySentMessage($sms);
    }
}
