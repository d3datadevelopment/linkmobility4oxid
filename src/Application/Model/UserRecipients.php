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
        $customFields = (oxNew(Configuration::class))->getUserRecipientFields();

        return count($customFields) ?
            $customFields :
            [
                'oxmobfon',
                'oxfon',
                'oxprivfon'
            ];
    }
}
