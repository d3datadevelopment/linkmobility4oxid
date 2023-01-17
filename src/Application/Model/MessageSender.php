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
use D3\LinkmobilityClient\LoggerHandler;
use Exception;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;

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
        try {
            if ($this->getConfiguration()->sendOrderFinishedMessage()) {
                $this->sendMessageByOrder($order, $messageBody);
            }
        } catch (noRecipientFoundException $e) {
            /** @var LoggerHandler $loggerHandler */
            $loggerHandler = d3GetOxidDIC()->get(LoggerHandler::class);
            $loggerHandler->getLogger()->debug($e->getMessage(), [$order]);
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
        try {
            if ($this->getConfiguration()->sendOrderSendedNowMessage()) {
                $this->sendMessageByOrder($order, $messageBody);
            }
        } catch (noRecipientFoundException $e) {
            /** @var LoggerHandler $loggerHandler */
            $loggerHandler = d3GetOxidDIC()->get(LoggerHandler::class);
            $loggerHandler->getLogger()->debug($e->getMessage(), [$order]);
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
        try {
            if ($this->getConfiguration()->sendOrderCanceledMessage()) {
                $this->sendMessageByOrder($order, $messageBody);
            }
        } catch (noRecipientFoundException $e) {
            /** @var LoggerHandler $loggerHandler */
            $loggerHandler = d3GetOxidDIC()->get(LoggerHandler::class);
            $loggerHandler->getLogger()->debug($e->getMessage(), [$order]);
        }
    }

    /**
     * @param Order  $order
     * @param string $messageBody
     *
     * @return void
     * @throws noRecipientFoundException
     */
    public function sendMessageByOrder(Order $order, string $messageBody): void
    {
        if ((bool) strlen(trim($messageBody)) === false) {
            return;
        }

        $sms = $this->getSms($messageBody);
        $sms->sendOrderMessage($order);
    }

    /**
     * @param string $message
     * @return Sms
     */
    protected function getSms(string $message): Sms
    {
        return oxNew(Sms::class, $message);
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
