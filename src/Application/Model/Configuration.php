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
use OxidEsales\Eshop\Core\Config;
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
        $token = $this->getConfig()->getConfigParam(self::GENERAL_APITOKEN);
        Assert::that($token)->string();
        $token = trim($token);
        Assert::that($token)->notEmpty();

        return $token;
    }

    /**
     * @return bool
     */
    public function getTestMode(): bool
    {
        return (bool) $this->getConfig()->getConfigParam(self::GENERAL_DEBUG);
    }

    /**
     * @return string|null
     */
    public function getSmsSenderNumber(): ?string
    {
        /** @var string $number */
        $number = $this->getConfig()->getConfigParam(self::SMS_SENDERNR);
        Assert::that($number)->string();
        $number = trim($number);

        return strlen($number) ? $number : null;
    }

    /**
     * @return string|null
     */
    public function getSmsSenderCountry(): ?string
    {
        /** @var string $country */
        $country = $this->getConfig()->getConfigParam(self::SMS_SENDERCOUNTRY);

        Assert::that($country)->string();

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
        $customFields = $this->getConfig()->getConfigParam(self::ORDER_RECFIELDS) ?: [];

        Assert::that($customFields)->isArray();

        array_walk(
            $customFields,
            [$this, 'checkFieldExists'],
            [self::ARGS_CHECKKEYS => true, self::ARGS_CHECKCLASS => Order::class]
        );

        // remove all false values and trim keys
        return array_filter($this->sanitizeKeys($customFields));
    }

    /**
     * @return string[]
     */
    public function getUserRecipientFields(): array
    {
        /** @var string[] $customFields */
        $customFields = $this->getConfig()->getConfigParam(self::USER_RECFIELDS) ?: [];

        Assert::that($customFields)->isArray();

        array_walk(
            $customFields,
            [$this, 'checkFieldExists'],
            [self::ARGS_CHECKKEYS => false, self::ARGS_CHECKCLASS => User::class]
        );

        // remove all false values
        return array_filter($customFields);
    }

    /**
     * @param array $customFields
     * @return array
     */
    public function sanitizeKeys(array $customFields): array
    {
        foreach ($customFields as $key => $value) {
            $sanitizedKey = trim($key);
            if ($key !== $sanitizedKey) {
                $customFields[$sanitizedKey] = $value;
                unset($customFields[$key]);
            }
        }
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

        $allFieldNames = oxNew($args[self::ARGS_CHECKCLASS])->getFieldNames() ?: [];

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
        return (bool) $this->getConfig()->getConfigParam(self::SENDBY_ORDERED);
    }

    /**
     * @return bool
     */
    public function sendOrderSendedNowMessage(): bool
    {
        return (bool) $this->getConfig()->getConfigParam(self::SENDBY_SENDEDNOW);
    }

    /**
     * @return bool
     */
    public function sendOrderCanceledMessage(): bool
    {
        return (bool) $this->getConfig()->getConfigParam(self::SENDBY_CANCELED);
    }

    /**
     * @return Config
     */
    protected function getConfig(): Config
    {
        /** @var Config $config */
        $config = d3GetOxidDIC()->get('d3ox.linkmobility.'.Config::class);
        return $config;
    }
}
