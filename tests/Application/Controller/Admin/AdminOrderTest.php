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

namespace D3\Linkmobility4OXID\Tests\Application\Controller\Admin;

use D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;

class AdminOrderTest extends d3ModCfgUnitTestCase
{
    /** @var AdminOrder */
    protected $controller;

    public function setUp()
    {
        parent::setUp();

        $this->controller = oxNew(AdminOrder::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->controller);
    }

    /**
     * @test
     * @return void
     * @throws \ReflectionException
     */
    public function testSend()
    {
        $this->callMethod(
            $this->controller,
            'send'
        );
    }
}