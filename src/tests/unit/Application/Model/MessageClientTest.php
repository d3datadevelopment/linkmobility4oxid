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

namespace D3\Linkmobility4OXID\tests\unit\Application\Model;

use D3\Linkmobility4OXID\Application\Model\Configuration;
use D3\Linkmobility4OXID\Application\Model\MessageClient;
use D3\Linkmobility4OXID\tests\unit\LMUnitTestCase;
use D3\LinkmobilityClient\Client;
use D3\LinkmobilityClient\LoggerHandler;
use D3\TestingTools\Development\CanAccessRestricted;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use ReflectionException;

class MessageClientTest extends LMUnitTestCase
{
    use CanAccessRestricted;

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\MessageClient::getClient
     */
    public function canGetClient()
    {
        /** @var Configuration|MockObject $configurationMock */
        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getApiToken'])
            ->getMock();
        $configurationMock->method('getApiToken')->willReturn('apiTokenFixture');
        d3GetOxidDIC()->set(Configuration::class, $configurationMock);

        /** @var MessageClient $sut */
        $sut = oxNew(MessageClient::class);

        $this->assertInstanceOf(
            Client::class,
            $this->callMethod(
                $sut,
                'getClient'
            )
        );

        /** @var LoggerHandler $loggerHandler */
        $loggerHandler = d3GetOxidDIC()->get(LoggerHandler::class);
        $this->assertInstanceOf(
            LoggerInterface::class,
            $loggerHandler->getLogger()
        );
    }
}
