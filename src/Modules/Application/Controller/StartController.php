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
use D3\LinkmobilityClient\Request\RequestInterface;
use D3\LinkmobilityClient\SMS\Request;
use D3\LinkmobilityClient\SMS\RequestFactory;
use D3\LinkmobilityClient\SMS\TextRequest;
use D3\LinkmobilityClient\ValueObject\Recipient;
use D3\LinkmobilityClient\ValueObject\Sender;
use D3\LinkmobilityClient\ValueObject\SmsMessage;
use OxidEsales\Eshop\Core\Registry;

class StartController extends StartController_parent
{
    public function render()
    {
        //$message = "testMessagetestMessagetestMessagetestMessagetestMessagetestMessage";
        $message = "test\tMessage\ttest\tMessage";

        $lmClient = oxNew(Client::class, trim(Registry::getConfig()->getConfigParam('d3linkmobility_apitoken')));
        $request = oxNew(RequestFactory::class, $message)->getRequest();
        $request->setTestMode( (bool) Registry::getConfig()->getConfigParam( 'd3linkmobility_debug'))
                ->setMethod(RequestInterface::METHOD_POST)
                ->setSenderAddress(oxNew(Sender::class, '017621164371', 'DE'))
                ->setSenderAddressType(RequestInterface::SENDERADDRESSTYPE_INTERNATIONAL);
        $request->getRecipientsList()
                                  ->add(oxNew(Recipient::class, '+49(0)176-21164371', 'DE'))
                                  ->add(oxNew(Recipient::class, '+49176 21164372', 'DE'))
                                  ->add(oxNew(Recipient::class, '03721268090', 'DE'))
                                  ->add(oxNew(Recipient::class, '0049176abc21164373', 'DE'));
        try {
            $response = $lmClient->request( $request );
        } catch (\Exception $e) {
            dumpvar($e->getMessage());
        }

        dumpvar($response->isSuccessful());
        dumpvar($response->getErrorMessage());
        dumpvar($response->getSmsCount());

        return parent::render();
    }
}