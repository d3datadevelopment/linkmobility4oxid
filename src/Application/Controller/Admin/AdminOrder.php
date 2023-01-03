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

use D3\DIContainerHandler\d3DicHandler;
use D3\Linkmobility4OXID\Application\Model\Exceptions\noRecipientFoundException;
use D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms;
use D3\Linkmobility4OXID\Application\Model\OrderRecipients;
use D3\TestingTools\Production\IsMockable;
use OxidEsales\Eshop\Application\Model\Order;

class AdminOrder extends AdminSendController
{
    use IsMockable;

    protected $_sThisTemplate = 'd3adminorder.tpl';

    /** @var Order */
    protected $item;

    /** @var OrderRecipients */
    protected $itemRecipients;

    public function __construct()
    {
        $this->item = d3GetOxidDIC()->get('d3ox.linkmobility.'.Order::class);
        d3GetOxidDIC()->set(OrderRecipients::class.'.args.order', $this->item);
        $this->itemRecipients = d3GetOxidDIC()->get(OrderRecipients::class);
        parent::__construct();
    }

    /**
     * @return string
     * @throws noRecipientFoundException
     */
    protected function sendMessage(): string
    {
        d3GetOxidDIC()->setParameter(Sms::class.'.args.message', $this->getMessageBody());
        /** @var Sms $sms */
        $sms = d3GetOxidDIC()->get(Sms::class);
        return $sms->sendOrderMessage($this->item) ?
            (string) $this->getSuccessSentMessage($sms) :
            $this->getUnsuccessfullySentMessage($sms);
    }
}
