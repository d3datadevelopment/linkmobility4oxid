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

namespace D3\Linkmobility4OXID\Application\Model;

use D3\Linkmobility4OXID\Application\Model\Exceptions\noRecipientFoundException;
use D3\LinkmobilityClient\Client;
use D3\LinkmobilityClient\Request\RequestInterface;
use D3\LinkmobilityClient\ValueObject\Sender;
use Exception;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Remark;
use OxidEsales\Eshop\Core\Registry;

class MessageSender
{
    /**
     * @param Order $order
     * @param       $messageBody
     *
     * @throws Exception
     */
    public function sendOrderMessage(Order $order, $messageBody)
    {
        if (false === (bool) Registry::getConfig()->getConfigParam('d3linkmobility_orderActive') ||
            trim(strlen($messageBody)) < 1
        ) {
            return;
        }

        try {
            $sms = oxNew( Sms::class );
            if ( $sms->sendOrderMessage( $order, $messageBody ) ) {
                $this->setRemark( $order->getId(), $messageBody );
            }
        } catch (noRecipientFoundException $e) {}
    }

    /**
     * @param $orderId
     * @param $message
     *
     * @throws Exception
     */
    protected function setRemark($orderId, $message)
    {
        $remark = oxNew( Remark::class );
        $remark->assign( [
             'oxtype'     => 'LMSMS',
             'oxparentid' => $orderId,
             'oxtext'     => $message
        ] );
        $remark->save();
    }

    public function sendContactMessage($email, $subject, $message)
    {
        $lmClient = oxNew(Client::class, 'token');
        $request = oxNew(Request::class, oxNew(Sender::class, 'sender'), oxNew(SmsMessage::class, $message));
        $request->setMethod(RequestInterface::METHOD_POST);
        $response = $lmClient->request($request);
        dumpvar($response);
    }
}