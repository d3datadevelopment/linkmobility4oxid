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

use D3\LinkmobilityClient\Exceptions\RecipientException;
use D3\LinkmobilityClient\Request\RequestInterface;
use D3\LinkmobilityClient\SMS\SmsRequestInterface;
use D3\LinkmobilityClient\ValueObject\Sender;
use D3\TestingTools\Production\IsMockable;
use libphonenumber\NumberParseException;

class RequestFactory extends \D3\LinkmobilityClient\SMS\RequestFactory
{
    use IsMockable;

    /**
     * @return SmsRequestInterface
     * @throws NumberParseException
     * @throws RecipientException
     */
    public function getSmsRequest(): SmsRequestInterface
    {
        /** @var Configuration $configuration */
        $configuration = d3GetOxidDIC()->get(Configuration::class);

        /** parent call */
        /** @var SmsRequestInterface $request */
        $request = $this->d3CallMockableFunction([\D3\LinkmobilityClient\SMS\RequestFactory::class, 'getSmsRequest']);

        $sender = $this->getSender($configuration->getSmsSenderNumber(), $configuration->getSmsSenderCountry());
        $request->setTestMode($configuration->getTestMode())
            ->setSenderAddress($sender)
            ->setSenderAddressType(RequestInterface::SENDERADDRESSTYPE_INTERNATIONAL);

        return $request;
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
}
