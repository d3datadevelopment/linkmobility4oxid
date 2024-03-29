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

// @codeCoverageIgnoreStart

namespace D3\Linkmobility4OXID\Setup;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Events
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function onActivate(): void
    {
        /** @var Actions $actions */
        $actions = d3GetOxidDIC()->get(Actions::class);
        $actions->setupDatabase();
        $actions->checkCmsItems();
        $actions->regenerateViews();
    }

    public static function onDeactivate(): void
    {
    }
}
// @codeCoverageIgnoreEnd
