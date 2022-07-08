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

use D3\Linkmobility4OXID\Application\Model\Exceptions\abortSendingExceptionInterface;
use D3\Linkmobility4OXID\Application\Model\Exceptions\noRecipientFoundException;
use D3\Linkmobility4OXID\Application\Model\RequestFactory;
use D3\LinkmobilityClient\Exceptions\ApiException;
use D3\LinkmobilityClient\Request\RequestInterface;
use D3\LinkmobilityClient\Response\ResponseInterface;
use D3\LinkmobilityClient\ValueObject\Recipient;
use D3\LinkmobilityClient\ValueObject\Sender;
use GuzzleHttp\Exception\GuzzleException;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;

class Sms
{
    private $response;
    private $recipients = [];
    private $message;
    protected $removeLineBreaks = true;
    protected $removeMultipleSpaces = true;

    public function __construct(string $message)
    {
        $this->message = $this->sanitizeMessage($message);
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function sendUserAccountMessage(User $user): bool
    {
        try {
            return $this->sendCustomRecipientMessage(
                [ oxNew( UserRecipients::class, $user )->getSmsRecipient() ]
            );
        } catch (noRecipientFoundException $e) {
            Registry::getLogger()->warning($e->getMessage());
            Registry::getUtilsView()->addErrorToDisplay($e);
        }

        return false;
    }

    /**
     * @param Order $order
     *
     * @return bool
     * @throws noRecipientFoundException
     */
    public function sendOrderMessage(Order $order): bool
    {
        try {
            oxNew( OrderRecipients::class, $order )->getSmsRecipient();
        } catch (Exception $e) {
            dumpvar($e->getMessage());
        }

        try {
            Registry::getLogger()->debug('startRequest', ['orderId' => $order->getId()]);
            $return = $this->sendCustomRecipientMessage(
                [ oxNew( OrderRecipients::class, $order )->getSmsRecipient() ]
            );
            Registry::getLogger()->debug('finishRequest', ['orderId' => $order->getId()]);
            return $return;
        } catch (noRecipientFoundException $e) {
            Registry::getLogger()->warning($e->getMessage());
            throw $e;
        }
    }

    /**
     * @param array $recipientsArray
     *
     * @return bool
     */
    public function sendCustomRecipientMessage(array $recipientsArray): bool
    {
        try {
            $this->setRecipients($recipientsArray);
            $configuration = oxNew( Configuration::class );
            $client        = oxNew( MessageClient::class )->getClient();

            $request = oxNew( RequestFactory::class, $this->getMessage(), $client )->getSmsRequest();
            $request->setTestMode( $configuration->getTestMode() )->setMethod( RequestInterface::METHOD_POST )->setSenderAddress( oxNew( Sender::class, $configuration->getSmsSenderNumber(), $configuration->getSmsSenderCountry() ) )->setSenderAddressType( RequestInterface::SENDERADDRESSTYPE_INTERNATIONAL );

            $recipientsList = $request->getRecipientsList();
            foreach ($recipientsArray as $recipient) {
                $recipientsList->add( $recipient );
            }

            $response = $client->request( $request );

            $this->response = $response;

            return $response->isSuccessful();
        } catch (abortSendingExceptionInterface $e) {
            Registry::getLogger()->warning($e->getMessage());
            Registry::getUtilsView()->addErrorToDisplay($e);
        } catch (GuzzleException $e) {
            Registry::getLogger()->warning($e->getMessage());
            Registry::getUtilsView()->addErrorToDisplay($e);
        } catch (ApiException $e) {
            Registry::getLogger()->warning($e->getMessage());
            Registry::getUtilsView()->addErrorToDisplay($e);
        } catch (\InvalidArgumentException $e) {
            Registry::getLogger()->warning($e->getMessage());
            Registry::getUtilsView()->addErrorToDisplay($e);
        }

        return false;
    }

    /**
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    protected function setRecipients(array $recipients)
    {
        $this->recipients = $recipients;
    }

    public function getRecipientsList() : string
    {
        $list = [];
        /** @var Recipient $recipient */
        foreach ($this->recipients as $recipient) {
            $list[] = $recipient->get();
        }

        return implode(', ', $list);
    }

    /**
     * @param $message
     *
     * @return string
     */
    protected function sanitizeMessage($message) : string
    {
        $message = trim(strip_tags($message));
        $message = $this->removeLineBreaks ? str_replace(["\r", "\n"], ' ', $message) : $message;
        $regexp = '/\s{2,}/m';
        return $this->removeMultipleSpaces ? preg_replace($regexp, ' ', $message) : $message;
    }

    public function getMessage() : string
    {
        return $this->message;
    }
}