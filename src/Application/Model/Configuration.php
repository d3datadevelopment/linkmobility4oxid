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
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;

class Configuration
{
    public const GENERAL_APITOKEN  = "d3linkmobility_apitoken";
    public const GENERAL_DEBUG     = "d3linkmobility_debug";

    public const ORDER_RECFIELDS   = "d3linkmobility_smsOrderRecipientsFields";
    public const USER_RECFIELDS    = "d3linkmobility_smsUserRecipientsFields";

    public const SMS_SENDERNR      = "d3linkmobility_smsSenderNumber";
    public const SMS_SENDERCOUNTRY = "d3linkmobility_smsSenderCountry";

    public const SENDBY_ORDERED    = "d3linkmobility_orderActive";
    public const SENDBY_SENDEDNOW  = "d3linkmobility_sendedNowActive";
    public const SENDBY_CANCELED   = "d3linkmobility_cancelOrderActive";

    public const ARGS_CHECKKEYS    = "checkKeys";
    public const ARGS_CHECKCLASS   = "checkClassName";

    /**
     * @return string
     */
    public function getApiToken(): string
    {
        /** @var string $token */
        $token = Registry::getConfig()->getConfigParam(self::GENERAL_APITOKEN);
        $token = trim($token);

        Assert::that($token)->string()->notEmpty();

        return $token;
    }

    /**
     * @return bool
     */
    public function getTestMode(): bool
    {
        return (bool) Registry::getConfig()->getConfigParam(self::GENERAL_DEBUG);
    }

    /**
     * @return string|null
     */
    public function getSmsSenderNumber()
    {
        /** @var string $number */
        $number = Registry::getConfig()->getConfigParam(self::SMS_SENDERNR);
        $number = trim($number);

        return strlen($number) ? $number : null;
    }

    /**
     * @return string|null
     */
    public function getSmsSenderCountry(): ?string
    {
        /** @var string $country */
        $country = Registry::getConfig()->getConfigParam(self::SMS_SENDERCOUNTRY);
        $country = trim($country);
        $country = strlen($country) ? strtoupper($country) : null;

        Assert::that($country)->nullOr()->string()->length(2);

        return $country;
    }

    /**
     * @return string[]
     */
    public function getOrderRecipientFields(): array
    {
        /** @var string[] $customFields */
        $customFields = Registry::getConfig()->getConfigParam(self::ORDER_RECFIELDS);

        array_walk(
            $customFields,
            [$this, 'checkFieldExists'],
            [self::ARGS_CHECKKEYS => true, self::ARGS_CHECKCLASS => Order::class]
        );
        $customFields = array_filter($customFields);

        Assert::that($customFields)->isArray();

        return $customFields;
    }

    /**
     * @return string[]
     */
    public function getUserRecipientFields(): array
    {
        /** @var string[] $customFields */
        $customFields = Registry::getConfig()->getConfigParam(self::USER_RECFIELDS);

        array_walk(
            $customFields,
            [$this, 'checkFieldExists'],
            [self::ARGS_CHECKKEYS => false, self::ARGS_CHECKCLASS => User::class]
        );
        $customFields = array_filter($customFields);

        Assert::that($customFields)->isArray();

        return $customFields;
    }

    /**
     * @template T
     * @param string $checkPhoneFieldName
     * @param string $checkCountryFieldName
     * @param array{checkKeys: bool, checkClassName: class-string<T>} $args
     * @return void
     */
    protected function checkFieldExists(string &$checkPhoneFieldName, string $checkCountryFieldName, array $args): void
    {
        $checkCountryFieldName = $args[self::ARGS_CHECKKEYS] ? trim($checkCountryFieldName) : $checkCountryFieldName;
        $checkPhoneFieldName = trim($checkPhoneFieldName);
        $allFieldNames = oxNew($args[self::ARGS_CHECKCLASS])->getFieldNames();

        array_walk($allFieldNames, function (&$value) {
            $value = strtolower($value);
        });

        $checkPhoneFieldName = in_array(strtolower($checkPhoneFieldName), $allFieldNames) && (
            false === $args[self::ARGS_CHECKKEYS] ||
            in_array(strtolower($checkCountryFieldName), $allFieldNames)
        ) ? $checkPhoneFieldName : null;
    }

    /**
     * @return bool
     */
    public function sendOrderFinishedMessage(): bool
    {
        return (bool) Registry::getConfig()->getConfigParam(self::SENDBY_ORDERED);
    }

    /**
     * @return bool
     */
    public function sendOrderSendedNowMessage(): bool
    {
        return (bool) Registry::getConfig()->getConfigParam(self::SENDBY_SENDEDNOW);
    }

    /**
     * @return bool
     */
    public function sendOrderCanceledMessage(): bool
    {
        return (bool) Registry::getConfig()->getConfigParam(self::SENDBY_CANCELED);
    }
}
