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
use D3\Linkmobility4OXID\Application\Model\Exceptions\successfullySentException;
use D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms;
use D3\Linkmobility4OXID\Application\Model\OrderRecipients;
use D3\LinkmobilityClient\Response\ResponseInterface;
use D3\LinkmobilityClient\ValueObject\Recipient;
use Exception;
use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;

class AdminOrder extends AdminController
{
    protected $_sThisTemplate = 'd3adminorder.tpl';

    /**
     * @var Sms
     */
    protected $sms;

    /**
     * @var Order
     */
    protected $order;

    public function __construct()
    {
        $this->order = $order = oxNew(Order::class);
        $order->load($this->getEditObjectId());

        $this->addTplParam('recipient', $this->getRecipientFromCurrentOrder());

        parent::__construct();
    }

    /**
     * @return Recipient|false
     */
    public function getRecipientFromCurrentOrder()
    {
        try {
            return oxNew(OrderRecipients::class, $this->order)->getSmsRecipient();
        } catch (noRecipientFoundException $e) {
            /** @var string $message */
            $message = Registry::getLang()->translateString($e->getMessage());
            Registry::getUtilsView()->addErrorToDisplay($message);
        }
        return false;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function send(): void
    {
        $messageBody = Registry::getRequest()->getRequestEscapedParameter('messagebody');

        if (false === is_string($messageBody) || strlen($messageBody) <= 1) {
            /** @var string $message */
            $message = Registry::getLang()->translateString('D3LM_EXC_MESSAGE_NO_LENGTH');
            Registry::getUtilsView()->addErrorToDisplay($message);
            return;
        }

        $order = oxNew(Order::class);
        $order->load($this->getEditObjectId());

        try {
            $sms = oxNew(Sms::class, $messageBody);
            if ($sms->sendOrderMessage($order)) {
                $smsCount = $sms->getResponse() ? $sms->getResponse()->getSmsCount() : 0;
                Registry::getUtilsView()->addErrorToDisplay(
                    oxNew(successfullySentException::class, $smsCount)
                );
            } else {
                $errorMsg = $sms->getResponse() instanceof ResponseInterface ? $sms->getResponse()->getErrorMessage() : 'no response';
                /** @var string $format */
                $format = Registry::getLang()->translateString('D3LM_EXC_MESSAGE_UNEXPECTED_ERR_SEND');
                Registry::getUtilsView()->addErrorToDisplay(sprintf($format, $errorMsg));
            }
        } catch (noRecipientFoundException $e) {
            Registry::getUtilsView()->addErrorToDisplay($e);
        }
    }
}
