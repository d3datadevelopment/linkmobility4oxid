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
use D3\Linkmobility4OXID\Application\Model\UserRecipients;
use D3\LinkmobilityClient\Response\ResponseInterface;
use D3\LinkmobilityClient\ValueObject\Recipient;
use Exception;
use InvalidArgumentException;
use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsView;

class AdminUser extends AdminController
{
    protected $_sThisTemplate = 'd3adminuser.tpl';

    /**
     * @var Sms
     */
    protected $sms;

    /** @var User */
    protected $item;

    /** @var UserRecipients */
    protected $itemRecipients;

    public function __construct()
    {
        /** @var User $item */
        $item = d3GetOxidDIC()->get('d3ox.linkmobility.'.User::class);
        $this->item = $item;
        d3GetOxidDIC()->set(UserRecipients::class.".args.user", $item);
        /** @var UserRecipients $itemRecipients */
        $itemRecipients = d3GetOxidDIC()->get(UserRecipients::class);
        $this->itemRecipients = $itemRecipients;
        $item->load($this->getEditObjectId());

        $this->addTplParam('recipient', $this->getRecipientFromCurrentItem());

        parent::__construct();
    }

    /**
     * @return string
     * @throws noRecipientFoundException
     * @throws Exception
     */
    protected function sendMessage(): string
    {
        $sms = $this->getSms($this->getMessageBody());
        return $sms->sendUserAccountMessage($this->item) ?
            $this->getSuccessSentMessage($sms)->getMessage() :
            $this->getUnsuccessfullySentMessage($sms);
    }

    /**
     * @param string $message
     * @return Sms
     */
    protected function getSms(string $message): Sms
    {
        return oxNew(Sms::class, $message);
    }

    /*** duplicated code but errors while phpunit coverage run if code is in shared abstract class or trait ***/

    /**
     * @return Recipient|false
     */
    public function getRecipientFromCurrentItem()
    {
        try {
            return $this->itemRecipients->getSmsRecipient();
        } catch (noRecipientFoundException $e) {
            /** @var Language $lang */
            $lang = d3GetOxidDIC()->get('d3ox.linkmobility.'.Language::class);
            /** @var string $message */
            $message = $lang->translateString($e->getMessage());
            /** @var UtilsView $utilsView */
            $utilsView = d3GetOxidDIC()->get('d3ox.linkmobility.'.UtilsView::class);
            $utilsView->addErrorToDisplay($message);
        }

        return false;
    }

    /**
     * @return void
     */
    public function send(): void
    {
        /** @var UtilsView $utilsView */
        $utilsView = d3GetOxidDIC()->get('d3ox.linkmobility.'.UtilsView::class);

        try {
            $utilsView->addErrorToDisplay($this->sendMessage());
        } catch (noRecipientFoundException|InvalidArgumentException $e) {
            $utilsView->addErrorToDisplay($e->getMessage());
        }
    }

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getMessageBody(): string
    {
        /** @var Request $request */
        $request = d3GetOxidDIC()->get('d3ox.linkmobility.'.Request::class);
        $messageBody = $request->getRequestEscapedParameter('messagebody');

        if (false === is_string($messageBody) || strlen(trim($messageBody)) <= 1) {
            /** @var InvalidArgumentException $exc */
            $exc = oxNew(
                InvalidArgumentException::class,
                Registry::getLang()->translateString('D3LM_EXC_MESSAGE_NO_LENGTH')
            );
            throw $exc;
        }

        return $messageBody;
    }

    /**
     * @param Sms $sms
     * @return successfullySentException
     */
    protected function getSuccessSentMessage(Sms $sms): successfullySentException
    {
        $smsCount = $sms->getResponse() ? $sms->getResponse()->getSmsCount() : 0;
        /** @var successfullySentException $exc */
        $exc = oxNew(successfullySentException::class, $smsCount);
        return $exc;
    }

    /**
     * @param Sms $sms
     * @return string
     */
    protected function getUnsuccessfullySentMessage(Sms $sms): string
    {
        $errorMsg = $sms->getResponse() instanceof ResponseInterface ? $sms->getResponse()->getErrorMessage() : 'no response';
        /** @var string $format */
        $format = Registry::getLang()->translateString('D3LM_EXC_MESSAGE_UNEXPECTED_ERR_SEND');
        return sprintf($format, $errorMsg);
    }
}
