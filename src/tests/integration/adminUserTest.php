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
use \D3\Linkmobility4OXID\Application\Controller\Admin\AdminUser;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use OxidEsales\Eshop\Application\Model\Remark;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\RequestInterface;

class adminUserTest extends LMIntegrationTestCase
{
    /** @var User */
    protected $user;
    protected $userId = 'testUserId';

    public function setUp(): void
    {
        parent::setUp();

        /** @var Configuration|MockObject $configuration */
        $configuration = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getTestMode'])
            ->getMock();
        $configuration->method('getTestMode')->willReturn(true);
        d3GetOxidDIC()->set(Configuration::class, $configuration);

        /** @var User $user */
        $this->user = $user = oxNew(User::class);
        $user->setId($this->userId);
        $user->assign([
            'oxfon' => '01512 3456789',
            'oxcountryid'   => 'a7c40f631fc920687.20179984'
        ]);
        $user->save();
    }

    /**
     * @test
     * @throws \Doctrine\DBAL\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
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

        $_POST['messagebody'] = 'testMessage';
        $_POST['oxid'] = $this->userId;

        /** @var AdminUser $controller */
        $controller = oxNew(AdminUser::class);
        $controller->send();

        // check requests
        $this->assertCount(
            1,
            $container
        );
        /** @var RequestInterface $request */
        $request = $container[0]['request'];
        $this->assertTrue(
            (bool) strpos(serialize($request->getBody()->getContents()), 'testMessage')
        );

        // check return message
        $search = sprintf(
            Registry::getLang()->translateString('D3LM_EXC_SMS_SUCC_SENT'),
            1
        );
        $this->assertTrue(
            (bool) strpos(serialize(Registry::getSession()->getVariable('Errors')), $search)
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
        $this->assertTrue(
            count($remarkIds) > 0
        );

        $this->deleteAllRemarksFrom($this->userId);
    }

    /**
     * @test
     * @throws \Doctrine\DBAL\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
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

        $_POST['messagebody'] = 'testMessage';
        $_POST['oxid'] = $this->userId;

        /** @var AdminUser $controller */
        $controller = oxNew(AdminUser::class);
        $controller->send();

        // check requests
        $this->assertCount(
            1,
            $container
        );
        /** @var RequestInterface $request */
        $request = $container[0]['request'];
        $this->assertTrue(
            (bool) strpos(serialize($request->getBody()->getContents()), 'testMessage')
        );

        // check return message
        $search = sprintf(
            Registry::getLang()->translateString('D3LM_EXC_MESSAGE_UNEXPECTED_ERR_SEND'),
            'parameter "messageContent" invalid'
        );

        $this->assertTrue(
            (bool) strpos(serialize(Registry::getSession()->getVariable('Errors')), $search)
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
        $this->assertTrue(
            count($remarkIds) == 0
        );

        $this->deleteAllRemarksFrom($this->userId);
    }

    /**
     * @test
     * @throws \Doctrine\DBAL\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function emptyMessage()
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

        $_POST['messagebody'] = '';
        $_POST['oxid'] = $this->userId;

        /** @var AdminUser $controller */
        $controller = oxNew(AdminUser::class);
        $controller->send();

        // check requests
        $this->assertCount(
            0,  // no request because of internal handling
            $container
        );

        // check return message
        $search = sprintf(
            Registry::getLang()->translateString('D3LM_EXC_MESSAGE_NO_LENGTH'),
            1
        );

        $this->assertTrue(
            (bool) strpos(serialize(Registry::getSession()->getVariable('Errors')), $search)
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
        $this->assertTrue(
            count($remarkIds) == 0
        );

        $this->deleteAllRemarksFrom($this->userId);
    }

    /**
     * @test
     * @throws \Doctrine\DBAL\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
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

        $_POST['messagebody'] = 'testMessage';
        $_POST['oxid'] = $this->userId;

        $this->user->assign([
            'oxfon' => '',
            'oxcountryid'   => ''
        ]);
        $this->user->save();

        /** @var AdminUser $controller */
        $controller = oxNew(AdminUser::class);
        $controller->send();

        // check requests
        $this->assertCount(
            0,  // no request because of internal handling
            $container
        );

        // check return message
        $search = sprintf(
            Registry::getLang()->translateString('D3LM_EXC_MESSAGE_UNEXPECTED_ERR_SEND'),
            'no response'
        );

        $this->assertTrue(
            (bool) strpos(serialize(Registry::getSession()->getVariable('Errors')), $search)
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
        $this->assertTrue(
            count($remarkIds) == 0
        );

        $this->deleteAllRemarksFrom($this->userId);
    }

    /**
     * @test
     * @throws \Doctrine\DBAL\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
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

        $_POST['messagebody'] = 'testMessage';
        $_POST['oxid'] = $this->userId;

        /** @var AdminUser $controller */
        $controller = oxNew(AdminUser::class);
        $controller->send();

        // check requests
        $this->assertCount(
            1,
            $container
        );
        /** @var RequestInterface $request */
        $request = $container[0]['request'];
        $this->assertTrue(
            (bool) strpos(serialize($request->getBody()->getContents()), 'testMessage')
        );

        // check return message
        $search = sprintf(
            Registry::getLang()->translateString('D3LM_EXC_MESSAGE_UNEXPECTED_ERR_SEND'),
            'no response'
        );

        $this->assertTrue(
            (bool) strpos(serialize(Registry::getSession()->getVariable('Errors')), $search)
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
        $this->assertTrue(
            count($remarkIds) == 0
        );

        $this->deleteAllRemarksFrom($this->userId);
    }

    /**
     * @throws Exception
     */
    public function tearDown(): void
    {
        parent::tearDown();

        $this->user->delete();
    }
}