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

namespace D3\Linkmobility4OXID\Modules\Application\Model;

use D3\Linkmobility4OXID\Modules\Core\EmailCore;
use D3\TestingTools\Production\IsMockable;
use OxidEsales\Eshop\Core\Email;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class OrderModel extends OrderModel_parent
{
    use IsMockable;

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function cancelOrder(): void
    {
        // parent::cancelOrder();
        $this->d3CallMockableFunction([OrderModel_parent::class, 'cancelOrder']);

        if ((bool) $this->getFieldData('oxstorno') === true) {
            /** @var EmailCore $Email */
            $Email = d3GetOxidDIC()->get('d3ox.linkmobility.'.Email::class);
            $Email->d3SendCancelMessage($this);
        }
    }
}
