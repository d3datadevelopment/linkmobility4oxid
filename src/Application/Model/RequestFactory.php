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
use D3\TestingTools\Production\IsMockable;

class RequestFactory extends \D3\LinkmobilityClient\SMS\RequestFactory
{
    use IsMockable;

    public function getSmsRequest(): SmsRequestInterface
    {
        /** @var Configuration $configuration */
        $configuration = d3GetOxidDIC()->get(Configuration::class);

        /** parent call */
        $request = $this->d3CallMockableFunction([\D3\LinkmobilityClient\SMS\RequestFactory::class, 'getSmsRequest']);

        d3GetOxidDIC()->setParameter(Sender::class.'.args.number', $configuration->getSmsSenderNumber());
        d3GetOxidDIC()->setParameter(Sender::class.'.args.iso2countrycode', $configuration->getSmsSenderCountry());
        $request->setTestMode($configuration->getTestMode())
            ->setSenderAddress(
                d3GetOxidDIC()->get(Sender::class)
            )
            ->setSenderAddressType(RequestInterface::SENDERADDRESSTYPE_INTERNATIONAL);

        return $request;
    }
}
