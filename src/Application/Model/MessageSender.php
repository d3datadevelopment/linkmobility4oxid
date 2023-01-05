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

class MessageSender
{
    /**
     * @param Order $order
     * @param string $messageBody
     * @return void
     * @throws Exception
     */
    public function sendOrderFinishedMessage(Order $order, string $messageBody): void
    {
        if ($this->getConfiguration()->sendOrderFinishedMessage()) {
            $this->sendMessageByOrder($order, $messageBody);
        }
    }

    /**
     * @param Order $order
     * @param string $messageBody
     * @return void
     * @throws Exception
     */
    public function sendSendedNowMessage(Order $order, string $messageBody): void
    {
        if ($this->getConfiguration()->sendOrderSendedNowMessage()) {
            $this->sendMessageByOrder($order, $messageBody);
        }
    }

    /**
     * @param Order $order
     * @param string $messageBody
     * @return void
     * @throws Exception
     */
    public function sendCancelOrderMessage(Order $order, string $messageBody): void
    {
        if ($this->getConfiguration()->sendOrderCanceledMessage()) {
            $this->sendMessageByOrder($order, $messageBody);
        }
    }

    /**
     * @param Order $order
     * @param string $messageBody
     * @return void
     * @throws Exception
     */
    public function sendMessageByOrder(Order $order, string $messageBody): void
    {
        if ((bool) strlen(trim($messageBody)) === false)
            return;

        try {
            d3GetOxidDIC()->setParameter(Sms::class.'.args.message', $messageBody);
            /** @var Sms $sms */
            $sms = d3GetOxidDIC()->get(Sms::class);
            $sms->sendOrderMessage($order);
        } catch (noRecipientFoundException $e) {
        }
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration(): Configuration
    {
        /** @var Configuration $configuration */
        $configuration = d3GetOxidDIC()->get(Configuration::class);
        return $configuration;
    }
}
