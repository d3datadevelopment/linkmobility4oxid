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

use D3\LinkmobilityClient\Client;
use D3\LinkmobilityClient\LoggerHandler;
use Psr\Log\LoggerInterface;

class MessageClient
{
    /**
     * @return Client
     */
    public function getClient(): Client
    {
        /** @var Configuration $configuration */
        $configuration = d3GetOxidDIC()->get(Configuration::class);

        d3GetOxidDIC()->setParameter(Client::class.'.args.accesstoken', $configuration->getApiToken());
        /** @var Client $client */
        $client = d3GetOxidDIC()->get(Client::class);

        /** @var LoggerHandler $loggerHandler */
        $loggerHandler = d3GetOxidDIC()->get(LoggerHandler::class);
        /** @var LoggerInterface $logger */
        $logger = d3GetOxidDIC()->get('d3ox.linkmobility.'.LoggerInterface::class);
        $loggerHandler->setLogger($logger);

        return $client;
    }
}
