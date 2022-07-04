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

namespace D3\Linkmobility4OXID\Modules\Application\Model;

use D3\Linkmobility4OXID\Application\Model\Configuration;
use D3\LinkmobilityClient\Request\RequestInterface;
use D3\LinkmobilityClient\SMS\SmsRequestInterface;
use D3\LinkmobilityClient\ValueObject\Sender;

class RequestFactory extends \D3\LinkmobilityClient\SMS\RequestFactory
{
    public function getSmsRequest(): SmsRequestInterface
    {
        $configuration = oxNew( Configuration::class );

        $request = parent::getSmsRequest();
        $request->setTestMode($configuration->getTestMode())
            ->setSenderAddress(
                oxNew( Sender::class, $configuration->getSmsSenderNumber(), $configuration->getSmsSenderCountry() )
            )
            ->setSenderAddressType( RequestInterface::SENDERADDRESSTYPE_INTERNATIONAL );

        return $request;
    }
}