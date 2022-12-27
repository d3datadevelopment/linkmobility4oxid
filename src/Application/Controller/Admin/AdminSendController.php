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
use D3\Linkmobility4OXID\Application\Model\UserRecipients;
use D3\LinkmobilityClient\Response\ResponseInterface;
use D3\LinkmobilityClient\ValueObject\Recipient;
use D3\TestingTools\Production\IsMockable;
use InvalidArgumentException;
use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsView;

abstract class AdminSendController extends AdminController
{
    use IsMockable;

    /**
     * @var Order|User
     */
    protected $item;

    /** @var OrderRecipients|UserRecipients */
    protected $itemRecipients;

    public function __construct()
    {
        $this->item->load($this->getEditObjectId());

        $this->addTplParam('recipient', $this->getRecipientFromCurrentItem());

        parent::__construct();
    }

    /**
     * @return Recipient|false
     */
    public function getRecipientFromCurrentItem()
    {
        try {
            return $this->itemRecipients->getSmsRecipient();
        } catch (noRecipientFoundException $e) {
            /** @var string $message */
            $message = $this->d3GetMockableRegistryObject(Language::class)->translateString($e->getMessage());
            $this->d3GetMockableRegistryObject(UtilsView::class)->addErrorToDisplay($message);
        }

        return false;
    }

    /**
     * @return void
     */
    public function send(): void
    {
        $utilsView = $this->d3GetMockableRegistryObject(UtilsView::class);

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
        $messageBody = $this->d3GetMockableRegistryObject(Request::class)
            ->getRequestEscapedParameter('messagebody');

        if (false === is_string($messageBody) || strlen(trim($messageBody)) <= 1) {
            throw $this->d3GetMockableOxNewObject(
                InvalidArgumentException::class,
                Registry::getLang()->translateString('D3LM_EXC_MESSAGE_NO_LENGTH')
            );
        }

        return $messageBody;
    }

    /**
     * @return string
     * @throws noRecipientFoundException
     */
    abstract protected function sendMessage(): string;

    /**
     * @param Sms $sms
     * @return successfullySentException
     */
    protected function getSuccessSentMessage(Sms $sms): successfullySentException
    {
        $smsCount = $sms->getResponse() ? $sms->getResponse()->getSmsCount() : 0;
        return $this->d3GetMockableOxNewObject(successfullySentException::class, $smsCount);
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