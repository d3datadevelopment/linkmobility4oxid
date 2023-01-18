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

namespace D3\Linkmobility4OXID\tests\integration;

use D3\Linkmobility4OXID\Application\Model\MessageClient;
use D3\Linkmobility4OXID\tests\unit\LMUnitTestCase;
use D3\LinkmobilityClient\Client;
use D3\LinkmobilityClient\LoggerHandler;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use OxidEsales\Eshop\Application\Model\Remark;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\NullLogger;

abstract class LMIntegrationTestCase extends LMUnitTestCase
{
    /**
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        d3GetOxidDIC()->get(LoggerHandler::class)->setLogger(new NullLogger());
    }

    /**
     * @param $userId
     *
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function deleteAllRemarksFrom($userId)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = ContainerFactory::getInstance()->getContainer()->get(QueryBuilderFactoryInterface::class)->create();
        $queryBuilder->delete(oxNew(Remark::class)->getCoreTableName())
        ->where(
            $queryBuilder->expr()->eq(
                'oxparentid',
                $queryBuilder->createNamedParameter($userId)
            )
        );
        $queryBuilder->execute();
    }

    /**
     * @param ResponseInterface|RequestException $response
     */
    protected function setClientResponse($response, callable $history = null)
    {
        $handlerMock = new MockHandler([$response]);

        $handlerStack = HandlerStack::create($handlerMock);

        if ($history) {
            $handlerStack->push($history);
        }

        $guzzleMock = new GuzzleClient(['handler' => $handlerStack]);

        /** @var Client $LMClient */
        $LMClient = oxNew(Client::class, 'accessToken', null, $guzzleMock);

        /** @var MessageClient|MockObject $messageClientMock */
        $messageClientMock = $this->getMockBuilder(MessageClient::class)
            ->onlyMethods(['getClient'])
            ->getMock();
        $messageClientMock->method('getClient')->willReturn($LMClient);

        d3GetOxidDIC()->set(MessageClient::class, $messageClientMock);
    }
}
