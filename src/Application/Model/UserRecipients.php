<?php

/**
 * This Software is the property of Data Development and is protected
 * by copyright law - it is NOT Freeware.
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 * http://www.shopmodule.com
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author        D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link          http://www.oxidmodule.com
 */

namespace D3\Linkmobility4OXID\Application\Model;

use D3\Linkmobility4OXID\Application\Model\Exceptions\noRecipientFoundException;
use D3\LinkmobilityClient\ValueObject\Recipient;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\User;

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
        foreach ($this->getSmsRecipientFields() as $fieldName)
        {
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
        return [
            'oxmobfon',
            'oxfon',
            'oxprivfon'
        ];
    }
}