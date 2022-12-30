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

namespace D3\Linkmobility4OXID\tests\unit\Modules;

use D3\Linkmobility4OXID\Modules\LinkmobilityServices;
use D3\Linkmobility4OXID\tests\unit\LMUnitTestCase;
use D3\TestingTools\Development\CanAccessRestricted;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class LinkMobilityServicesTest extends LMUnitTestCase
{
    use CanAccessRestricted;

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Modules\LinkmobilityServices::__construct
     */
    public function canConstruct()
    {
        /** @var LinkmobilityServices|MockObject $sut */
        $sut = $this->getMockBuilder(LinkmobilityServices::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addYamlDefinitions'])
            ->getMock();
        $sut->expects($this->atLeastOnce())->method('addYamlDefinitions')->with(
            $this->identicalTo('d3/linkmobility/Config/services.yaml')
        );

        $this->callMethod(
            $sut,
            '__construct'
        );
    }
}