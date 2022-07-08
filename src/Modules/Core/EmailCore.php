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

namespace D3\Linkmobility4OXID\Modules\Core;

use D3\Linkmobility4OXID\Application\Model\MessageSender;
use Exception;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\EshopCommunity\Internal\Framework\Templating\TemplateRendererBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Templating\TemplateRendererInterface;

class EmailCore extends EmailCore_parent
{
    protected $d3OrderCustSmsTemplate = 'd3sms_ordercust.tpl';
    protected $d3OrderSendedNowSmsTemplate = 'd3sms_sendednow.tpl';
    protected $d3OrderCanceledSmsTemplate = 'd3sms_ordercanceled.tpl';

    /**
     * @param Order $order
     * @param null  $subject
     *
     * @return bool
     */
    public function sendOrderEmailToUser($order, $subject = null)
    {
        $ret = parent::sendOrderEmailToUser($order, $subject);

        $this->d3SendOrderFinishedMessageToUser($order);

        return $ret;
    }

    /**
     * @param Order $order
     * @param null  $subject
     *
     * @return bool
     */
    public function sendSendedNowMail($order, $subject = null)
    {
        $ret = parent::sendSendedNowMail($order, $subject);

        $this->d3SendedNowMessage($order);

        return $ret;
    }

    /**
     * @param Order $order
     *
     * @throws Exception
     */
    public function d3SendOrderFinishedMessageToUser(Order $order)
    {
        $messageSender = oxNew(MessageSender::class);
        $messageSender->sendOrderFinishedMessage($order, $this->d3GetOrderFinishedSmsMessageBody($order));
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    protected function d3GetOrderFinishedSmsMessageBody(Order $order): string
    {
        $renderer = $this->d3GetTplRenderer();
        $this->setViewData("order", $order);

        return $renderer->renderTemplate($this->d3OrderCustSmsTemplate, $this->getViewData());
    }

    /**
     * @param Order $order
     *
     * @throws Exception
     */
    public function d3SendedNowMessage(Order $order)
    {
        $messageSender = oxNew(MessageSender::class);
        $messageSender->sendSendedNowMessage($order, $this->d3GetSendedNowSmsMessageBody($order));
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    protected function d3GetSendedNowSmsMessageBody(Order $order): string
    {
        $renderer = $this->d3GetTplRenderer();
        $this->setViewData("order", $order);

        return $renderer->renderTemplate($this->d3OrderSendedNowSmsTemplate, $this->getViewData());
    }

    public function d3SendCancelMessage($order)
    {
        $messageSender = oxNew(MessageSender::class);
        $messageSender->sendCancelOrderMessage($order, $this->d3GetCancelOrderSmsMessageBody($order));
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    protected function d3GetCancelOrderSmsMessageBody(Order $order): string
    {
        $renderer = $this->d3GetTplRenderer();
        $this->setViewData("order", $order);

        return $renderer->renderTemplate($this->d3OrderCanceledSmsTemplate, $this->getViewData());
    }

    /**
     * Templating instance getter
     *
     * @return TemplateRendererInterface
     */
    protected function d3GetTplRenderer() : TemplateRendererInterface
    {
        $bridge = \OxidEsales\EshopCommunity\Internal\Container\ContainerFactory::getInstance()->getContainer()
            ->get(TemplateRendererBridgeInterface::class);
        $bridge->setEngine($this->_getSmarty());

        return $bridge->getTemplateRenderer();
    }
}