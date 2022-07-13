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
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;

class UserRecipients
{
    /**
     * @var User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return Recipient
     * @throws noRecipientFoundException
     */
    public function getSmsRecipient(): Recipient
    {
        foreach ($this->getSmsRecipientFields() as $fieldName) {
            $content = trim($this->user->getFieldData($fieldName));
            if (strlen($content)) {
                $country = oxNew(Country::class);
                $country->load($this->user->getFieldData('oxcountryid'));

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
                'oxmobfon',
                'oxfon',
                'oxprivfon'
            ];
    }

    /**
     * @return array
     */
    public function getSanitizedCustomFields(): array
    {
        $customFields = (array) Registry::getConfig()->getConfigParam('d3linkmobility_smsUserRecipientsFields');
        array_walk($customFields, [$this, 'checkFieldExists']);
        return array_filter($customFields);
    }

    /**
     * @param $checkFieldName
     */
    public function checkFieldExists(&$checkFieldName)
    {
        $checkFieldName = trim($checkFieldName);
        $allFieldNames = oxNew(User::class)->getFieldNames();

        array_walk($allFieldNames, function (&$value) {
            $value = strtolower($value);
        });

        $checkFieldName = in_array(strtolower($checkFieldName), $allFieldNames) ? $checkFieldName : null;
    }
}
