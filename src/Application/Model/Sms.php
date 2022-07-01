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
use D3\LinkmobilityClient\Exceptions\ApiException;
use D3\LinkmobilityClient\Request\RequestInterface;
use D3\LinkmobilityClient\SMS\RequestFactory;
use D3\LinkmobilityClient\ValueObject\Sender;
use GuzzleHttp\Exception\GuzzleException;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;

class Sms
{
    /**
     * @param User $user
     * @param      $message
     *
     * @return bool
     */
    public function sendUserAccountMessage(User $user, $message): bool
    {
        try {
            $configuration = oxNew( Configuration::class );
            $client        = oxNew( MessageClient::class )->getClient();

            $request = oxNew( RequestFactory::class, $message, $client )->getSmsRequest();
            $request->setTestMode( $configuration->getTestMode() )->setMethod( RequestInterface::METHOD_POST )->setSenderAddress( oxNew( Sender::class, $configuration->getSmsSenderNumber(), $configuration->getSmsSenderCountry() ) )->setSenderAddressType( RequestInterface::SENDERADDRESSTYPE_INTERNATIONAL );

            $recipientsList = $request->getRecipientsList();
            $recipientsList->add(oxNew( UserRecipients::class, $user )->getSmsRecipient());
            $response = $client->request( $request );

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
}