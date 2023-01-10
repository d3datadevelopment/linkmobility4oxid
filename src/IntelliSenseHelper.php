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

namespace D3\Linkmobility4OXID\Modules {

    use D3\DIContainerHandler\definitionFileContainer;

    class LinkmobilityServices_parent extends definitionFileContainer
    {
    }
}

namespace D3\Linkmobility4OXID\Modules\Application\Model {

    use OxidEsales\Eshop\Application\Model\Order;

    class OrderModel_parent extends Order
    {
    }
}

namespace D3\Linkmobility4OXID\Modules\Core {

    use OxidEsales\Eshop\Core\Email;

    class EmailCore_parent extends Email
    {
    }
}
