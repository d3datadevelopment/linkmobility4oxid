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

namespace D3\Linkmobility4OXID\Application\Model;

use Assert\Assert;
use OxidEsales\Eshop\Core\Registry;

class Configuration
{
    /**
     * @return string
     */
    public function getApiToken(): string
    {
        $token = trim(Registry::getConfig()->getConfigParam('d3linkmobility_apitoken'));

        Assert::that($token)->string()->notEmpty();

        return $token;
    }

    /**
     * @return bool
     */
    public function getTestMode(): bool
    {
        return (bool) Registry::getConfig()->getConfigParam( 'd3linkmobility_debug');
    }

    /**
     * @return string
     */
    public function getSmsSenderNumber(): string
    {
        $number = trim(Registry::getConfig()->getConfigParam('d3linkmobility_smsSenderNumber'));

        Assert::that($number)->string()->notEmpty();

        return $number;
    }

    /**
     * @return string
     */
    public function getSmsSenderCountry(): string
    {
        $country = trim(Registry::getConfig()->getConfigParam('d3linkmobility_smsSenderCountry'));

        Assert::that($country)->string()->length(2);

        return strtoupper($country);
    }
}