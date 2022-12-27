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

namespace D3\Linkmobility4OXID\Application\Model;

use D3\LinkmobilityClient\Request\RequestInterface;
use D3\LinkmobilityClient\SMS\SmsRequestInterface;
use D3\LinkmobilityClient\ValueObject\Sender;

class RequestFactory extends \D3\LinkmobilityClient\SMS\RequestFactory
{
    public function getSmsRequest(): SmsRequestInterface
    {
        $configuration = oxNew(Configuration::class);

        $request = parent::getSmsRequest();
        $request->setTestMode($configuration->getTestMode())
            ->setSenderAddress(
                oxNew(Sender::class, $configuration->getSmsSenderNumber(), $configuration->getSmsSenderCountry())
            )
            ->setSenderAddressType(RequestInterface::SENDERADDRESSTYPE_INTERNATIONAL);

        return $request;
    }
}
