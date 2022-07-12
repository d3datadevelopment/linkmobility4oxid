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

namespace D3\Linkmobility4OXID\Application\Controller\Admin;

use D3\Linkmobility4OXID\Application\Model\Exceptions\noRecipientFoundException;
use D3\Linkmobility4OXID\Application\Model\Exceptions\successfullySentException;
use D3\Linkmobility4OXID\Application\Model\OrderRecipients;
use D3\Linkmobility4OXID\Application\Model\Sms;
use D3\Linkmobility4OXID\Application\Model\UserRecipients;
use D3\LinkmobilityClient\ValueObject\Recipient;
use Exception;
use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Remark;
use OxidEsales\Eshop\Application\Model\User;
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
            return oxNew( OrderRecipients::class, $this->order )->getSmsRecipient();
        } catch (noRecipientFoundException $e) {
            Registry::getUtilsView()->addErrorToDisplay(
                Registry::getLang()->translateString($e->getMessage())
            );
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public function send()
    {
        $messageBody = Registry::getRequest()->getRequestEscapedParameter( 'messagebody' );

        if (strlen($messageBody) <= 1) {
            Registry::getUtilsView()->addErrorToDisplay(
                Registry::getLang()->translateString('D3LM_EXC_MESSAGE_NO_LENGTH')
            );
            return;
        }

        $order = oxNew(Order::class);
        $order->load($this->getEditObjectId());

        try {
            $sms = oxNew( Sms::class, $messageBody );
            if ( $sms->sendOrderMessage( $order ) ) {
                $this->setRemark( $sms->getRecipientsList(), $sms->getMessage() );
                Registry::getUtilsView()->addErrorToDisplay(
                    oxNew(successfullySentException::class, $sms->getResponse()->getSmsCount() )
                );
            } else {
                Registry::getUtilsView()->addErrorToDisplay(
                    sprintf(
                        Registry::getLang()->translateString( 'D3LM_EXC_MESSAGE_UNEXPECTED_ERR_SEND' ),
                        $sms->getResponse()->getErrorMessage()
                    )
                );
            }
        } catch (noRecipientFoundException $e) {
            Registry::getUtilsView()->addErrorToDisplay($e);
        }
    }

    /**
     * @param $messageBody
     *
     * @throws Exception
     */
    protected function setRemark( $recipients, $messageBody )
    {
        $remark = oxNew( Remark::class );
        $remark->assign( [
            'oxtype'     => AdminUser::REMARK_IDENT,
            'oxparentid' => $this->order->getUser()->getId(),
            'oxtext'     => $recipients.PHP_EOL.$messageBody
        ] );
        $remark->save();
    }
}