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

use D3\Linkmobility4OXID\Application\Model\Exceptions\noRecipientFoundException;
use D3\Linkmobility4OXID\tests\unit\LMUnitTestCase;
use D3\TestingTools\Development\CanAccessRestricted;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class noRecipientFoundExceptionTest extends LMUnitTestCase
{
    use CanAccessRestricted;

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Application\Model\Exceptions\noRecipientFoundException::__construct
     */
    public function canConstruct()
    {
        /** @var noRecipientFoundException|MockObject $sut */
        $sut = $this->getMockBuilder(noRecipientFoundException::class)
            ->getMock();

        $this->assertRegExp(
            '@.*NO.*RECIPIENT.*SET.*@',
            $this->callMethod(
                $sut,
                'getMessage'
            )
        );
    }
}