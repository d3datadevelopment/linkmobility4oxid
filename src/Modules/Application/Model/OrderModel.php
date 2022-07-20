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
use OxidEsales\Eshop\Core\Email;

class OrderModel extends OrderModel_parent
{
    public function cancelOrder()
    {
        parent::cancelOrder();

        if ($this->getFieldData('oxstorno') === 1) {
            /** @var EmailCore $Email */
            $Email = oxNew(Email::class);
            $Email->d3SendCancelMessage($this);
        }
    }
}
