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
use D3\Linkmobility4OXID\Application\Model\MessageTypes\Sms;
use D3\Linkmobility4OXID\Application\Model\UserRecipients;
use D3\LinkmobilityClient\Response\ResponseInterface;
use D3\LinkmobilityClient\ValueObject\Recipient;
use Exception;
use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;

class AdminUser extends AdminController
{
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
            return oxNew(UserRecipients::class, $this->user)->getSmsRecipient();
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
        $messageBody = Registry::getRequest()->getRequestEscapedParameter('messagebody');

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
            Registry::getUtilsView()->addErrorToDisplay(
                sprintf(
                    Registry::getLang()->translateString('D3LM_EXC_SMS_SUCC_SENT'),
                    $sms->getResponse()->getSmsCount()
                )
            );
        } else {
            $errorMsg = $sms->getResponse() instanceof ResponseInterface ? $sms->getResponse()->getErrorMessage() : 'no response';
            Registry::getUtilsView()->addErrorToDisplay(
                sprintf(
                    Registry::getLang()->translateString('D3LM_EXC_MESSAGE_UNEXPECTED_ERR_SEND'),
                    $errorMsg
                )
            );
        }
    }
}
