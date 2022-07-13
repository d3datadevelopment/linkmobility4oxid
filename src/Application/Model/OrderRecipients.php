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

use D3\Linkmobility4OXID\Application\Model\Exceptions\noRecipientFoundException;
use D3\LinkmobilityClient\ValueObject\Recipient;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;

class OrderRecipients
{
    /**
     * @var Order
     */
    private $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return Recipient
     * @throws noRecipientFoundException
     */
    public function getSmsRecipient(): Recipient
    {
        foreach ($this->getSmsRecipientFields() as $phoneFieldName => $countryIdFieldName)
        {
            $content = trim($this->order->getFieldData($phoneFieldName));

            if (strlen($content)) {
                $country = oxNew(Country::class);
                $country->load($this->order->getFieldData($countryIdFieldName));

                return oxNew(Recipient::class, $content, $country->getFieldData('oxisoalpha2'));
            }
        }

        throw oxNew(noRecipientFoundException::class);
    }

    /**
     * @return string[]
     */
    public function getSmsRecipientFields(): array
    {
        $customFields = $this->getSanitizedCustomFields();

        return count($customFields) ?
            $customFields :
            [
                'oxdelfon'  => 'oxdelcountryid',
                'oxbillfon' => 'oxbillcountryid'
            ];
    }

    /**
     * @return array
     */
    public function getSanitizedCustomFields() : array
    {
        $customFields = (array) Registry::getConfig()->getConfigParam('d3linkmobility_smsOrderRecipientsFields');
        array_walk($customFields, [$this, 'checkFieldExists']);
        return array_filter($customFields);
    }

    public function checkFieldExists(&$checkPhoneFieldName, $checkCountryFieldName)
    {
        $checkCountryFieldName = trim($checkCountryFieldName);
        $checkPhoneFieldName = trim($checkPhoneFieldName);
        $allFieldNames = oxNew(Order::class)->getFieldNames();

        array_walk($allFieldNames, function(&$value) {$value = strtolower($value);});

        $checkPhoneFieldName = in_array(strtolower($checkPhoneFieldName), $allFieldNames) &&
               in_array(strtolower($checkCountryFieldName), $allFieldNames) ? $checkPhoneFieldName : null;
    }
}