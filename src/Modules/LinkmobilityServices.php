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

namespace D3\Linkmobility4OXID\Modules;

class LinkmobilityServices extends LinkmobilityServices_parent
{
    public function __construct()
    {
        parent::__construct();
        $this->addYamlDefinitions('d3/linkmobility/Config/services.yaml');
    }
}