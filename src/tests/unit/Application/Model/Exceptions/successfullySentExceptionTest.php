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

namespace D3\Linkmobility4OXID\tests\unit\Application\Model\Exceptions;

use D3\Linkmobility4OXID\Application\Model\Exceptions\successfullySentException;
use D3\Linkmobility4OXID\tests\unit\LMUnitTestCase;
use D3\TestingTools\Development\CanAccessRestricted;
use OxidEsales\Eshop\Core\Language;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class successfullySentExceptionTest extends LMUnitTestCase
{
    use CanAccessRestricted;

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\Exceptions\successfullySentException::__construct
     */
    public function canConstruct()
    {
        /** @var Language|MockObject $languageMock */
        $languageMock = $this->getMockBuilder(Language::class)
            ->onlyMethods(['translateString'])
            ->getMock();
        $languageMock->method('translateString')->willReturn('%1$s messages');
        d3GetOxidDIC()->set('d3ox.linkmobility.'.Language::class, $languageMock);

        /** @var successfullySentException|MockObject $sut */
        $sut = $this->getMockBuilder(successfullySentException::class)
            ->setConstructorArgs([25, 10])
            ->getMock();

        $this->assertSame(
            '25 messages',
            $this->callMethod(
                $sut,
                'getMessage'
            )
        );
    }
}