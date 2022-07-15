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

namespace D3\Linkmobility4OXID\Application\Model;

use D3\Linkmobility4OXID\Application\Model\Exceptions\noRecipientFoundException;
use D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms;
use Exception;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;

class MessageSender
{
    /**
     * @param Order $order
     * @param       $messageBody
     * @throws Exception
     */
    public function sendOrderFinishedMessage(Order $order, $messageBody)
    {
        $this->sendMessageByOrder('d3linkmobility_orderActive', $order, $messageBody);
    }

    /**
     * @param Order $order
     * @param       $messageBody
     * @throws Exception
     */
    public function sendSendedNowMessage(Order $order, $messageBody)
    {
        $this->sendMessageByOrder('d3linkmobility_sendedNowActive', $order, $messageBody);
    }

    /**
     * @param Order $order
     * @param       $messageBody
     * @throws Exception
     */
    public function sendCancelOrderMessage(Order $order, $messageBody)
    {
        $this->sendMessageByOrder('d3linkmobility_cancelOrderActive', $order, $messageBody);
    }

    /**
     * @param       $configParam
     * @param Order $order
     * @param       $messageBody
     * @throws Exception
     */
    public function sendMessageByOrder($configParam, Order $order, $messageBody)
    {
        if (false === (bool) Registry::getConfig()->getConfigParam($configParam)
            || (bool) strlen(trim($messageBody)) === false
        ) {
            return;
        }

        try {
            $sms = oxNew(Sms::class, $messageBody);
            $sms->sendOrderMessage($order);
        } catch (noRecipientFoundException $e) {
        }
    }
}
