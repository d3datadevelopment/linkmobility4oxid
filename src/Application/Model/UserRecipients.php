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
     * @param string $fieldName
     * @return Recipient|null
     */
    protected function getSmsRecipientByField(string $fieldName): ?Recipient
    {
        /** @var Country $country */
        $country = d3GetOxidDIC()->get('d3ox.linkmobility.'.Country::class);

        try {
            /** @var string $content */
            $content = $this->user->getFieldData($fieldName) ?: '';
            $content = trim($content);

            if (strlen($content)) {
                /** @var string $countryId */
                $countryId = $this->user->getFieldData('oxcountryid');
                $country->load($countryId);
                return $this->getRecipient($content, (string) $country->getFieldData('oxisoalpha2'));
            }
        } catch (NumberParseException|RecipientException $e) {
            /** @var LoggerHandler $loggerHandler */
            $loggerHandler = d3GetOxidDIC()->get(LoggerHandler::class);
            $loggerHandler->getLogger()->info(
                $e->getMessage(),
                [$content, $country->getFieldData('oxisoalpha2')]
            );
        }

        return null;
    }

    /**
     * @param string $content
     * @param string $countryCode
     * @throws NumberParseException
     * @throws RecipientException
     * @return Recipient
     */
    protected function getRecipient(string $content, string $countryCode): Recipient
    {
        return oxNew(Recipient::class, $content, $countryCode);
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
