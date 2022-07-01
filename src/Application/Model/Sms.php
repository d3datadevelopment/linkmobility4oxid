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

use D3\LinkmobilityClient\Request\RequestInterface;
use D3\LinkmobilityClient\SMS\RequestFactory;
use D3\LinkmobilityClient\ValueObject\Recipient;
use D3\LinkmobilityClient\ValueObject\Sender;
use OxidEsales\Eshop\Application\Model\User;

class Sms
{
    public function sendMessageToUser(User $user, $message)
    {
        $configuration = oxNew(Configuration::class);
        $client = oxNew(MessageClient::class)->getClient();

        $request = oxNew(RequestFactory::class, $message, $client)->getSmsRequest();
        $request->setTestMode( $configuration->getTestMode())
            ->setMethod(RequestInterface::METHOD_POST)
            ->setSenderAddress(
                oxNew(Sender::class, $configuration->getSmsSenderNumber(), $configuration->getSmsSenderCountry())
            )
            ->setSenderAddressType(RequestInterface::SENDERADDRESSTYPE_INTERNATIONAL);

        $recipientsList = $request->getRecipientsList();
        dumpvar($recipientsList);
        /*
        foreach (oxNew(UserRecipients::class)->getSmsRecipients() as $recipient) {
            $recipientsList->
        }
        */
        /*
            ->add(oxNew(Recipient::class, '+49(0)176-21164371', 'DE'))
            ->add(oxNew(Recipient::class, '+49176 21164372', 'DE'))
            ->add(oxNew(Recipient::class, '03721268090', 'DE'))
            ->add(oxNew(Recipient::class, '0049176abc21164373', 'DE'));
        */
    }
}