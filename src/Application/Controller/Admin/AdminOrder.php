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
use D3\Linkmobility4OXID\Application\Model\OrderRecipients;
use D3\LinkmobilityClient\Response\ResponseInterface;
use D3\LinkmobilityClient\ValueObject\Recipient;
use D3\TestingTools\Production\IsMockable;
use InvalidArgumentException;
use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsView;

class AdminOrder extends AdminController
{
    use IsMockable;

    protected $_sThisTemplate = 'd3adminorder.tpl';

    /** @var Order */
    protected $item;

    /** @var OrderRecipients */
    protected $itemRecipients;

    public function __construct()
    {
        $this->item = d3GetOxidDIC()->get('d3ox.linkmobility.'.Order::class);
        d3GetOxidDIC()->set(OrderRecipients::class.'.args.order', $this->item);
        $this->itemRecipients = d3GetOxidDIC()->get(OrderRecipients::class);

        $this->item->load($this->getEditObjectId());

        $this->addTplParam('recipient', $this->getRecipientFromCurrentItem());

        parent::__construct();
    }

    /**
     * @return string
     * @throws noRecipientFoundException
     */
    protected function sendMessage(): string
    {
        d3GetOxidDIC()->setParameter(Sms::class.'.args.message', $this->getMessageBody());
        /** @var Sms $sms */
        $sms = d3GetOxidDIC()->get(Sms::class);
        return $sms->sendOrderMessage($this->item) ?
            (string) $this->getSuccessSentMessage($sms) :
            $this->getUnsuccessfullySentMessage($sms);
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
            $utilsView->addErrorToDisplay($e);
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
            d3GetOxidDIC()->setParameter(
                'd3ox.linkmobility.'.InvalidArgumentException::class.'.args.message',
                Registry::getLang()->translateString('D3LM_EXC_MESSAGE_NO_LENGTH')
            );
            /** @var InvalidArgumentException $exc */
            $exc = d3GetOxidDIC()->get('d3ox.linkmobility.'.InvalidArgumentException::class);
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
        d3GetOxidDIC()->setParameter(successfullySentException::class.'.args.smscount', $smsCount);
        /** @var successfullySentException $exc */
        $exc = d3GetOxidDIC()->get(successfullySentException::class);
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
