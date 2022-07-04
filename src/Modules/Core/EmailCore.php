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
    protected $orderCustSmsTemplate = 'd3sms_ordercust.tpl';

    /**
     * @param Order $order
     * @param null  $subject
     *
     * @return bool
     */
    public function sendOrderEmailToUser($order, $subject = null)
    {
        $ret = parent::sendOrderEmailToUser($order, $subject);

        $this->d3SendOrderMessage($order);

        return $ret;
    }

    /**
     * @param Order $order
     *
     * @throws Exception
     */
    public function d3SendOrderMessage(Order $order)
    {
        dumpvar($this->d3GetOrderSmsMessageBody($order));
        die();
        $messageSender = oxNew(MessageSender::class);
        $messageSender->sendOrderMessage($order, $this->d3GetOrderSmsMessageBody($order));
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    protected function d3GetOrderSmsMessageBody(Order $order): string
    {
        $renderer = $this->d3GetTplRenderer();
        $this->setViewData("order", $order);

        dumpvar($this->orderCustSmsTemplate);

        return $renderer->renderTemplate($this->orderCustSmsTemplate, $this->getViewData());
    }

    /**
     * Templating instance getter
     *
     * @return TemplateRendererInterface
     */
    protected function d3GetTplRenderer()
    {
        $bridge = \OxidEsales\EshopCommunity\Internal\Container\ContainerFactory::getInstance()->getContainer()->get(TemplateRendererBridgeInterface::class);
        $bridge->setEngine($this->_getSmarty());

        return $bridge->getTemplateRenderer();
    }
}