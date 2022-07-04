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

namespace D3\Linkmobility4OXID\Modules\Application\Controller;

use D3\LinkmobilityClient\Client;
use D3\LinkmobilityClient\SMS\Request;
use D3\LinkmobilityClient\ValueObject\Sender;
use D3\LinkmobilityClient\ValueObject\SmsMessage;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Domain\Contact\Form\ContactFormBridgeInterface;

class ContactController extends ContactController_parent
{
    public function send()
    {
        $contactFormBridge = $this->getContainer()->get(ContactFormBridgeInterface::class);

        $form = $contactFormBridge->getContactForm();
        $form->handleRequest($this->getMappedContactFormRequest());

        if ($form->isValid()) {
            $contactMessageSender = oxNew(MessageSender::class);
            $contactMessageSender->send(
                $form->email->getValue(),
                $form->subject->getValue(),
                $contactFormBridge->getContactFormMessage($form)
            );
        } else {
            foreach ($form->getErrors() as $error) {
                Registry::getUtilsView()->addErrorToDisplay($error);
            }

            return false;
        }
    }
}