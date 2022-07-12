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
use D3\Linkmobility4OXID\Application\Model\Sms;
use D3\Linkmobility4OXID\Application\Model\UserRecipients;
use D3\LinkmobilityClient\ValueObject\Recipient;
use Exception;
use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Application\Model\Remark;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;

class AdminUser extends AdminController
{
    const REMARK_IDENT = 'LMSMS';
    
    protected $_sThisTemplate = 'd3adminuser.tpl';

    /**
     * @var Sms
     */
    protected $sms;

    /**
     * @var User
     */
    protected $user;

    public function __construct()
    {
        $this->user = $user = oxNew(User::class);
        $user->load($this->getEditObjectId());

        $this->addTplParam('recipient', $this->getRecipientFromCurrentUser());

        parent::__construct();
    }

    /**
     * @return Recipient|false
     */
    public function getRecipientFromCurrentUser()
    {
        try {
            return oxNew( UserRecipients::class, $this->user )->getSmsRecipient();
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

        $user = oxNew(User::class);
        $user->load($this->getEditObjectId());

        $sms = oxNew(Sms::class, $messageBody);
        if ($sms->sendUserAccountMessage($user)) {
            $this->setRemark( $sms->getRecipientsList(), $sms->getMessage() );
            Registry::getUtilsView()->addErrorToDisplay(
                sprintf(
                    Registry::getLang()->translateString('D3LM_EXC_SMS_SUCC_SENT'),
                    $sms->getResponse()->getSmsCount()
                )
            );
        } else {
            Registry::getUtilsView()->addErrorToDisplay(
                sprintf(
                    Registry::getLang()->translateString( 'D3LM_EXC_MESSAGE_UNEXPECTED_ERR_SEND' ),
                    $sms->getResponse()->getErrorMessage()
                )
            );
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
            'oxparentid' => $this->getEditObjectId(),
            'oxtext'     => $recipients.PHP_EOL.$messageBody
        ] );
        $remark->save();
    }
}