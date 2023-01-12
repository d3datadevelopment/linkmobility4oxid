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
use D3\LinkmobilityClient\Client;
use D3\LinkmobilityClient\Exceptions\ApiException;
use D3\LinkmobilityClient\Exceptions\RecipientException;
use D3\LinkmobilityClient\RecipientsList\RecipientsList;
use D3\LinkmobilityClient\RecipientsList\RecipientsListInterface;
use D3\LinkmobilityClient\Request\RequestInterface;
use D3\LinkmobilityClient\Response\ResponseInterface;
use D3\LinkmobilityClient\SMS\SmsRequestInterface;
use D3\LinkmobilityClient\ValueObject\Recipient;
use D3\LinkmobilityClient\ValueObject\Sender;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use libphonenumber\NumberParseException;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsView;
use Psr\Log\LoggerInterface;

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
            $this->getLogger()->debug('startRequest', ['userId' => $user->getId()]);
            $return = $this->sendCustomRecipientMessage($this->getUserRecipientsList($user));
            if ($return) {
                $this->setRemark($user->getId(), $this->getRecipientsList(), $this->getMessage());
            }
            $this->getLogger()->debug('finishRequest', ['userId' => $user->getId()]);
            return $return;
        } catch (noRecipientFoundException $e) {
            $this->getLogger()->warning($e->getMessage());
            /** @var UtilsView $utilsView */
            $utilsView = d3GetOxidDIC()->get('d3ox.linkmobility.'.UtilsView::class);
            $utilsView->addErrorToDisplay($e);
        }

        return false;
    }

    /**
     * @param User $user
     * @return RecipientsListInterface
     * @throws noRecipientFoundException
     */
    protected function getUserRecipientsList(User $user): RecipientsListInterface
    {
        /** @var MessageClient $messageClient */
        $messageClient = d3GetOxidDIC()->get(MessageClient::class);

        d3GetOxidDIC()->set(RecipientsList::class.'.args.client', $messageClient->getClient());
        /** @var RecipientsList $recipientsList */
        $recipientsList = d3GetOxidDIC()->get(RecipientsList::class);

        d3GetOxidDIC()->set(UserRecipients::class.'.args.user', $user);
        /** @var UserRecipients $userRecipients */
        $userRecipients = d3GetOxidDIC()->get(UserRecipients::class);
        return $recipientsList->add(
            $userRecipients->getSmsRecipient()
        );
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
            $this->getLogger()->debug('startRequest', ['orderId' => $order->getId()]);
            $return = $this->sendCustomRecipientMessage($this->getOrderRecipientsList($order));
            if ($return) {
                $this->setRemark($order->getOrderUser()->getId(), $this->getRecipientsList(), $this->getMessage());
            }
            $this->getLogger()->debug('finishRequest', ['orderId' => $order->getId()]);
            return $return;
        } catch (noRecipientFoundException $e) {
            $this->getLogger()->warning($e->getMessage());
            /** @var UtilsView $utilsView */
            $utilsView = d3GetOxidDIC()->get('d3ox.linkmobility.'.UtilsView::class);
            $utilsView->addErrorToDisplay($e);
        }

        return false;
    }

    /**
     * @param Order $order
     * @return RecipientsListInterface
     * @throws noRecipientFoundException
     */
    protected function getOrderRecipientsList(Order $order): RecipientsListInterface
    {
        /** @var MessageClient $messageClient */
        $messageClient = d3GetOxidDIC()->get(MessageClient::class);

        d3GetOxidDIC()->set(RecipientsList::class.'.args.client', $messageClient->getClient());
        /** @var RecipientsList $recipientsList */
        $recipientsList = d3GetOxidDIC()->get(RecipientsList::class);

        return $recipientsList->add(
            $this->getOrderRecipient(($order))
        );
    }

    /**
     * @param Order $order
     * @return Recipient
     * @throws noRecipientFoundException
     */
    protected function getOrderRecipient(Order $order): Recipient
    {
        d3GetOxidDIC()->set(OrderRecipients::class.'.args.order', $order);
        /** @var OrderRecipients $orderRecipients */
        $orderRecipients = d3GetOxidDIC()->get(OrderRecipients::class);
        return $orderRecipients->getSmsRecipient();
    }

    /**
     * @param RecipientsListInterface $recipientsList
     *
     * @return bool
     */
    public function sendCustomRecipientMessage(RecipientsListInterface $recipientsList): bool
    {
        try {
            $this->response = $response = $this->submitMessage($recipientsList);

            return $response->isSuccessful();
        } catch (abortSendingExceptionInterface|GuzzleException|ApiException|InvalidArgumentException $e) {
            $this->getLogger()->warning($e->getMessage());
            // Oxid does not accept throwable interface only exceptions according to definition
            /** @var UtilsView $utilsView */
            $utilsView = d3GetOxidDIC()->get('d3ox.linkmobility.'.UtilsView::class);
            $utilsView->addErrorToDisplay($e->getMessage());
        }

        return false;
    }

    /**
     * @param Configuration $configuration
     * @param Client $client
     * @return SmsRequestInterface
     * @throws NumberParseException
     * @throws RecipientException
     */
    protected function getRequest(Configuration $configuration, Client $client): SmsRequestInterface
    {
        $requestFactory = $this->getRequestFactory($this->getMessage(), $client);
        $sender = $this->getSender($configuration->getSmsSenderNumber(), $configuration->getSmsSenderCountry());

        /** @var SmsRequestInterface $request */
        $request = $requestFactory->getSmsRequest();
        $request->setTestMode($configuration->getTestMode())->setMethod(RequestInterface::METHOD_POST)
            ->setSenderAddress($sender)
            ->setSenderAddressType(RequestInterface::SENDERADDRESSTYPE_INTERNATIONAL);

        return $request;
    }

    /**
     * @param string $message
     * @param Client $client
     * @return RequestFactory
     */
    protected function getRequestFactory(string $message, Client $client): RequestFactory
    {
        return oxNew(RequestFactory::class, $message, $client);
    }

    /**
     * @param string $number
     * @param string $countryCode
     * @throws NumberParseException
     * @throws RecipientException
     * @return Sender
     */
    protected function getSender(string $number, string $countryCode): Sender
    {
        return oxNew(Sender::class, $number, $countryCode);
    }

    /**
     * @param RecipientsListInterface $recipientsList
     * @return ResponseInterface
     * @throws ApiException
     * @throws GuzzleException
     */
    protected function submitMessage(RecipientsListInterface $recipientsList): ResponseInterface
    {
        $this->setRecipients($recipientsList);
        /** @var Configuration $configuration */
        $configuration = d3GetOxidDIC()->get(Configuration::class);
        /** @var MessageClient $messageClient */
        $messageClient = d3GetOxidDIC()->get(MessageClient::class);
        $client        = $messageClient->getClient();

        $request = $this->getRequest($configuration, $client);
        $requestRecipientsList = $request->getRecipientsList();
        foreach ($recipientsList->getRecipients() as $recipient) {
            $requestRecipientsList->add($recipient);
        }

        $response = $client->request($request);

        if (false === $response->isSuccessful()) {
            $this->getLogger()->warning($response->getErrorMessage(), [$request->getBody()]);
        }

        return $response;
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return 'SMS';
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return Registry::getLogger();
    }
}
