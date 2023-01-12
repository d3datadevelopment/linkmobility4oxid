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
use D3\Linkmobility4OXID\Application\Model\RequestFactory;
use D3\Linkmobility4OXID\tests\unit\LMUnitTestCase;
use D3\LinkmobilityClient\SMS\SmsRequestInterface;
use D3\LinkmobilityClient\SMS\TextRequest;
use D3\LinkmobilityClient\ValueObject\Sender;
use D3\TestingTools\Development\CanAccessRestricted;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class RequestFactoryTest extends LMUnitTestCase
{
    use CanAccessRestricted;

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\RequestFactory::getSmsRequest
     */
    public function canGetSmsRequest()
    {
        /** @var Configuration|MockObject $configurationMock */
        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getTestMode', 'getSmsSenderNumber', 'getSmsSenderCountry'])
            ->getMock();
        $configurationMock->expects($this->once())->method('getTestMode')->willReturn(true);
        $configurationMock->expects($this->once())->method('getSmsSenderNumber')->willReturn('01512 3456789');
        $configurationMock->expects($this->once())->method('getSmsSenderCountry')->willReturn('DE');
        d3GetOxidDIC()->set(Configuration::class, $configurationMock);

        /** @var Sender|MockObject $senderMock */
        $senderMock = $this->getMockBuilder(Sender::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var TextRequest|MockObject $textRequestMock */
        $textRequestMock = $this->getMockBuilder(TextRequest::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var RequestFactory|MockObject $sut */
        $sut = $this->getMockBuilder(RequestFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['d3CallMockableFunction', 'getSender'])
            ->getMock();
        $sut->method('d3CallMockableFunction')->willReturn($textRequestMock);
        $sut->method('getSender')->willReturn($senderMock);

        $this->assertInstanceOf(
            SmsRequestInterface::class,
            $this->callMethod(
                $sut,
                'getSmsRequest'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\RequestFactory::getSender
     */
    public function canGetSender()
    {
        /** @var RequestFactory|MockObject $sut */
        $sut = $this->getMockBuilder(RequestFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(
            Sender::class,
            $this->callMethod(
                $sut,
                'getSender',
                ['01512 3456789', 'DE']
            )
        );
    }
}
