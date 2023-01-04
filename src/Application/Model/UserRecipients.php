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
use D3\LinkmobilityClient\Exceptions\RecipientException;
use D3\LinkmobilityClient\LoggerHandler;
use D3\LinkmobilityClient\ValueObject\Recipient;
use libphonenumber\NumberParseException;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\User;

class UserRecipients
{
    /**
     * @var User
     */
    protected $user;

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
            if ($recipient = $this->getSmsRecipientByField($fieldName)) {
                return $recipient;
            }
        }

        /** @var noRecipientFoundException $exc */
        $exc = d3GetOxidDIC()->get(noRecipientFoundException::class);
        throw $exc;
    }

    /**
     * @param $fieldName
     * @return Recipient|null
     */
    protected function getSmsRecipientByField($fieldName): ?Recipient
    {
        try {
            /** @var string $content */
            $content = $this->user->getFieldData($fieldName) ?: '';
            $content = trim($content);

            if (strlen($content)) {
                $country = d3GetOxidDIC()->get('d3ox.linkmobility.'.Country::class);
                /** @var string $countryId */
                $countryId = $this->user->getFieldData('oxcountryid');
                $country->load($countryId);
                d3GetOxidDIC()->setParameter(Recipient::class.'.args.number', $content);
                d3GetOxidDIC()->setParameter(Recipient::class.'.args.iso2countrycode', $country->getFieldData('oxisoalpha2'));
                /** @var Recipient $recipient */
                $recipient = d3GetOxidDIC()->get(Recipient::class);
                return $recipient;
            }
        } catch (NumberParseException|RecipientException $e) {
            d3GetOxidDIC()->get(LoggerHandler::class)->getLogger()->info(
                $e->getMessage(),
                [$content, $country->getFieldData('oxisoalpha2')]
            );
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function getSmsRecipientFields(): array
    {
        /** @var Configuration $configuration */
        $configuration = d3GetOxidDIC()->get(Configuration::class);
        $customFields = $configuration->getUserRecipientFields();

        return count($customFields) ?
            $customFields :
            [
                'oxmobfon',
                'oxfon',
                'oxprivfon'
            ];
    }
}
