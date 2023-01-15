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

namespace D3\Linkmobility4OXID\tests\integration;

use D3\Linkmobility4OXID\Application\Model\Configuration;
use Doctrine\DBAL\Exception as DoctrineException;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Remark;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\RequestInterface;

class canceledOrderTest extends LMIntegrationTestCase
{
    /** @var Order */
    protected $order;
    protected $userId = 'testUserId';
    protected $orderId = 'testOrderId';

    public function setUp(): void
    {
        parent::setUp();

        /** @var Configuration|MockObject $configuration */
        $configuration = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getTestMode', 'sendOrderSendedNowMessage', 'sendOrderCanceledMessage', 'sendOrderFinishedMessage'])
            ->getMock();
        $configuration->method('getTestMode')->willReturn(true);
        $configuration->method('sendOrderSendedNowMessage')->willReturn(false);
        $configuration->method('sendOrderCanceledMessage')->willReturn(true);
        $configuration->method('sendOrderFinishedMessage')->willReturn(false);
        d3GetOxidDIC()->set(Configuration::class, $configuration);

        /** @var User $user */
        $this->user = $user = oxNew(User::class);
        $user->setId($this->userId);
        $user->save();
        
        /** @var Order $order */
        $this->order = $order = oxNew( Order::class);
        $order->setId($this->orderId);
        $order->assign([
            'oxbillfon'         => '01512 3456789',
            'oxbillcountryid'   => 'a7c40f631fc920687.20179984',
            'oxbillfname'       => 'John',
            'oxbilllname'       => 'Doe',
            'oxbillcompany'     => '',
            'oxbillstreet'      => '',
            'oxbillstreetnr'    => '',
            'oxbillzip'         => '',
            'oxbillcity'        => '',
            'oxuserid'          => $this->userId
        ]);
        $order->save();
    }

    /**
     * @test
     * @throws DoctrineException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function succSending()
    {
        $container  =  [];
        $history  =  Middleware::history($container);

        $this->deleteAllRemarksFrom($this->userId);

        $this->setClientResponse(
            new Response(
                200,
                ['X-Foo' => 'Bar'],
                '{"statusCode":2000,"statusMessage":"OK","clientMessageId":null,"transferId":"0063c11b0100eeda5a1d","smsCount":1}'
            ),
            $history
        );

        $this->order->cancelOrder();

        // check requests
        $this->assertCount(
            1,
            $container
        );
        
        /** @var RequestInterface $request */
        $request = $container[0]['request'];
        $this->assertTrue(
            (bool) strpos(serialize($request->getBody()->getContents()), 'John Doe')
        );

        // check remark
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = ContainerFactory::getInstance()->getContainer()->get(QueryBuilderFactoryInterface::class)->create();
        $queryBuilder->select('oxid')
            ->from(oxNew(Remark::class)->getViewName())
            ->where(
                $queryBuilder->expr()->eq(
                    'oxparentid',
                    $queryBuilder->createNamedParameter($this->userId)
                )
            );
        $remarkIds = $queryBuilder->execute()->fetchAll();
        $this->assertNotEmpty($remarkIds);

        $this->deleteAllRemarksFrom($this->userId);
    }

    /**
     * @test
     * @throws DoctrineException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function unknownNumberError()
    {
        $container  =  [];
        $history  =  Middleware::history($container);

        $this->deleteAllRemarksFrom($this->userId);

        $this->setClientResponse(
            new Response(
                200,
                ['X-Foo' => 'Bar'],
                '{"statusCode":2000,"statusMessage":"OK","clientMessageId":null,"transferId":"0063c11b0100eeda5a1d","smsCount":1}'
            ),
            $history
        );

        $this->order->assign( [
            'oxbillfon'         => '222',
            'oxbillcountryid'   => 'a7c40f631fc920687.20179984',
        ]);
        $this->order->save();

        $this->order->cancelOrder();

        // check requests
        $this->assertCount(
            0,  // no request because of internal handling
            $container
        );

        // check remark
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = ContainerFactory::getInstance()->getContainer()->get(QueryBuilderFactoryInterface::class)->create();
        $queryBuilder->select('oxid')
            ->from(oxNew(Remark::class)->getViewName())
            ->where(
                $queryBuilder->expr()->eq(
                    'oxparentid',
                    $queryBuilder->createNamedParameter($this->userId)
                )
            );
        $remarkIds = $queryBuilder->execute()->fetchAll();
        $this->assertEmpty($remarkIds);

        $this->deleteAllRemarksFrom($this->userId);
    }

    /**
     * @test
     * @throws DoctrineException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function apiError()
    {
        $container  =  [];
        $history  =  Middleware::history($container);

        $this->deleteAllRemarksFrom($this->userId);

        $this->setClientResponse(
            new Response(
                200,
                [],
                '{"statusCode": 4019, "statusMessage": "parameter \"messageContent\" invalid", "clientMessageId": null, "transferId": null, "smsCount": 0}'
            ),
            $history
        );

        $this->order->cancelOrder();

        // check requests
        $this->assertCount(
            1,
            $container
        );
        /** @var RequestInterface $request */
        $request = $container[0]['request'];
        $this->assertTrue(
            (bool) strpos(serialize($request->getBody()->getContents()), 'John Doe')
        );

        // check remark
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = ContainerFactory::getInstance()->getContainer()->get(QueryBuilderFactoryInterface::class)->create();
        $queryBuilder->select('oxid')
                     ->from(oxNew(Remark::class)->getViewName())
                     ->where(
                         $queryBuilder->expr()->eq(
                             'oxparentid',
                             $queryBuilder->createNamedParameter($this->userId)
                         )
                     );
        $remarkIds = $queryBuilder->execute()->fetchAll();
        $this->assertEmpty($remarkIds);

        $this->deleteAllRemarksFrom($this->userId);
    }

    /**
     * @test
     * @throws DoctrineException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function notConfigured()
    {
        $container  =  [];
        $history  =  Middleware::history($container);

        $this->deleteAllRemarksFrom($this->userId);

        $this->setClientResponse(
            new Response(
                200,
                [],
                '{"statusCode": 4019, "statusMessage": "parameter \"messageContent\" invalid", "clientMessageId": null, "transferId": null, "smsCount": 0}'
            ),
            $history
        );

        /** @var Configuration|MockObject $configuration */
        $configuration = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getTestMode', 'sendOrderSendedNowMessage', 'sendOrderCanceledMessage', 'sendOrderFinishedMessage'])
            ->getMock();
        $configuration->method('getTestMode')->willReturn(true);
        $configuration->method('sendOrderSendedNowMessage')->willReturn(false);
        $configuration->method('sendOrderCanceledMessage')->willReturn(false);
        $configuration->method('sendOrderFinishedMessage')->willReturn(false);
        d3GetOxidDIC()->set(Configuration::class, $configuration);

        $this->order->cancelOrder();

        // check requests
        $this->assertCount(
            0,  // no request because of internal handling
            $container
        );

        // check remark
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = ContainerFactory::getInstance()->getContainer()->get(QueryBuilderFactoryInterface::class)->create();
        $queryBuilder->select('oxid')
                     ->from(oxNew(Remark::class)->getViewName())
                     ->where(
                         $queryBuilder->expr()->eq(
                             'oxparentid',
                             $queryBuilder->createNamedParameter($this->userId)
                         )
                     );
        $remarkIds = $queryBuilder->execute()->fetchAll();
        $this->assertEmpty($remarkIds);

        $this->deleteAllRemarksFrom($this->userId);
    }

    /**
     * @test
     * @throws DoctrineException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function recipientError()
    {
        $container  =  [];
        $history  =  Middleware::history($container);

        $this->deleteAllRemarksFrom($this->userId);

        $this->setClientResponse(
            new Response(
                200,
                ['X-Foo' => 'Bar'],
                '{"statusCode":2000,"statusMessage":"OK","clientMessageId":null,"transferId":"0063c11b0100eeda5a1d","smsCount":1}'
            ),
            $history
        );

        $this->order->assign( [
            'oxbillfon' => '',
            'oxbillcountryid'   => ''
        ]);
        $this->order->save();

        $this->order->cancelOrder();

        // check requests
        $this->assertCount(
            0,  // no request because of internal handling
            $container
        );

        // check remark
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = ContainerFactory::getInstance()->getContainer()->get(QueryBuilderFactoryInterface::class)->create();
        $queryBuilder->select('oxid')
                     ->from(oxNew(Remark::class)->getViewName())
                     ->where(
                         $queryBuilder->expr()->eq(
                             'oxparentid',
                             $queryBuilder->createNamedParameter($this->userId)
                         )
                     );
        $remarkIds = $queryBuilder->execute()->fetchAll();
        $this->assertEmpty($remarkIds);

        $this->deleteAllRemarksFrom($this->userId);
    }

    /**
     * @test
     * @throws DoctrineException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function communicationError()
    {
        $container  =  [];
        $history  =  Middleware::history($container);

        $this->deleteAllRemarksFrom($this->userId);

        $this->setClientResponse(
            new RequestException(
                'Error Communicating with Server',
                new Request('GET', 'test')
            ),
            $history
        );

        $this->order->cancelOrder();

        // check requests
        $this->assertCount(
            1,
            $container
        );
        /** @var RequestInterface $request */
        $request = $container[0]['request'];
        $this->assertTrue(
            (bool) strpos(serialize($request->getBody()->getContents()), 'John Doe')
        );

        // check remark
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = ContainerFactory::getInstance()->getContainer()->get(QueryBuilderFactoryInterface::class)->create();
        $queryBuilder->select('oxid')
                     ->from(oxNew(Remark::class)->getViewName())
                     ->where(
                         $queryBuilder->expr()->eq(
                             'oxparentid',
                             $queryBuilder->createNamedParameter($this->userId)
                         )
                     );
        $remarkIds = $queryBuilder->execute()->fetchAll();
        $this->assertEmpty($remarkIds);

        $this->deleteAllRemarksFrom($this->userId);
    }

    /**
     * @throws Exception
     */
    public function tearDown(): void
    {
        parent::tearDown();

        $this->order->delete();
        $this->user->delete();
    }
}