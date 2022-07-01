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

    public function getSmsRecipients()
    {
        dumpvar(array_map(
            function ($fieldName) {
dumpvar($fieldName);
            },
            $this->getSmsRecipientFields()
        ));
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

    public function getSmsCountry()
    {

    }
}