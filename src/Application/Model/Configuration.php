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
    public const DEBUG = "d3linkmobility_debug";

    /**
     * @return string
     */
    public function getApiToken(): string
    {
        $token = trim((string) Registry::getConfig()->getConfigParam('d3linkmobility_apitoken'));

        Assert::that($token)->string()->notEmpty();

        return $token;
    }

    /**
     * @return bool
     */
    public function getTestMode(): bool
    {
        return (bool) Registry::getConfig()->getConfigParam(self::DEBUG);
    }

    /**
     * @return string|null
     */
    public function getSmsSenderNumber()
    {
        $number = trim(Registry::getConfig()->getConfigParam('d3linkmobility_smsSenderNumber'));

        return strlen($number) ? $number : null;
    }

    /**
     * @return string|null
     */
    public function getSmsSenderCountry(): string
    {
        $country = trim(Registry::getConfig()->getConfigParam('d3linkmobility_smsSenderCountry'));
        $country = strlen($country) ? strtoupper($country) : null;

        Assert::that($country)->nullOr()->string()->length(2);

        return $country;
    }
}
