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

namespace D3\Linkmobility4OXID\Application\Model\MessageTypes;

use D3\Linkmobility4OXID\Application\Model\Configuration;
use D3\Linkmobility4OXID\Application\Model\Exceptions\abortSendingExceptionInterface;
use D3\Linkmobility4OXID\Application\Model\Exceptions\noRecipientFoundException;
use D3\Linkmobility4OXID\Application\Model\MessageClient;
use D3\Linkmobility4OXID\Application\Model\OrderRecipients;
use D3\Linkmobility4OXID\Application\Model\RequestFactory;
use D3\Linkmobility4OXID\Application\Model\UserRecipients;
use D3\LinkmobilityClient\Exceptions\ApiException;
use D3\LinkmobilityClient\Request\RequestInterface;
use D3\LinkmobilityClient\SMS\SmsRequestInterface;
use D3\LinkmobilityClient\ValueObject\Recipient;
use D3\LinkmobilityClient\ValueObject\Sender;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;

class Sms extends AbstractMessage
{
    /**
     * @param User $user
     *
     * @return bool
     * @throws Exception
     */
    public function sendUserAccountMessage(User $user): bool
    {
        try {
            Registry::getLogger()->debug('startRequest', ['userId' => $user->getId()]);
            $return = $this->sendCustomRecipientMessage(
                [ oxNew(UserRecipients::class, $user)->getSmsRecipient() ]
            );
            if ($return) {
                $this->setRemark($user->getId(), $this->getRecipientsList(), $this->getMessage());
            }
            Registry::getLogger()->debug('finishRequest', ['userId' => $user->getId()]);
            return $return;
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
     * @throws Exception
     */
    public function sendOrderMessage(Order $order): bool
    {
        try {
            Registry::getLogger()->debug('startRequest', ['orderId' => $order->getId()]);
            $return = $this->sendCustomRecipientMessage(
                [ $this->getOrderRecipient($order) ]
            );
            if ($return) {
                $this->setRemark($order->getOrderUser()->getId(), $this->getRecipientsList(), $this->getMessage());
            }
            Registry::getLogger()->debug('finishRequest', ['orderId' => $order->getId()]);
            return $return;
        } catch (noRecipientFoundException $e) {
            Registry::getLogger()->warning($e->getMessage());
            throw $e;
        }
    }

    /**
     * @param Order $order
     * @return Recipient
     * @throws noRecipientFoundException
     */
    protected function getOrderRecipient(Order $order): Recipient
    {
        return oxNew(OrderRecipients::class, $order)->getSmsRecipient();
    }

    /**
     * @param array<Recipient> $recipientsArray
     *
     * @return bool
     */
    public function sendCustomRecipientMessage(array $recipientsArray): bool
    {
        try {
            $this->setRecipients($recipientsArray);
            $configuration = oxNew(Configuration::class);
            $client        = oxNew(MessageClient::class)->getClient();

            /** @var SmsRequestInterface $request */
            $request = oxNew(RequestFactory::class, $this->getMessage(), $client)->getSmsRequest();
            $request->setTestMode($configuration->getTestMode())->setMethod(RequestInterface::METHOD_POST)
                ->setSenderAddress(
                    oxNew(
                        Sender::class,
                        $configuration->getSmsSenderNumber(),
                        $configuration->getSmsSenderCountry()
                    )
                )
                ->setSenderAddressType(RequestInterface::SENDERADDRESSTYPE_INTERNATIONAL);

            $recipientsList = $request->getRecipientsList();
            foreach ($recipientsArray as $recipient) {
                $recipientsList->add($recipient);
            }

            $response = $client->request($request);

            $this->response = $response;

            if (false === $response->isSuccessful()) {
                Registry::getLogger()->warning($response->getErrorMessage(), [$request->getBody()]);
            }

            return $response->isSuccessful();
        } catch (abortSendingExceptionInterface $e) {
            Registry::getLogger()->warning($e->getMessage());
            // Oxid does not accept throwable interface only exceptions according to definition
            Registry::getUtilsView()->addErrorToDisplay($e->getMessage());
        } catch (GuzzleException $e) {
            Registry::getLogger()->warning($e->getMessage());
            Registry::getUtilsView()->addErrorToDisplay($e->getMessage());
        } catch (ApiException $e) {
            Registry::getLogger()->warning($e->getMessage());
            Registry::getUtilsView()->addErrorToDisplay($e->getMessage());
        } catch (InvalidArgumentException $e) {
            Registry::getLogger()->warning($e->getMessage());
            Registry::getUtilsView()->addErrorToDisplay($e->getMessage());
        }

        return false;
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return 'SMS';
    }
}
