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
use OxidEsales\Eshop\Application\Model\Order;

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
        foreach ($this->getSmsRecipientFields() as $phoneFieldName => $countryIdFieldName) {
            $content = trim((string) $this->order->getFieldData($phoneFieldName));
            $country = oxNew(Country::class);

            try {
                if (strlen($content)) {
                    $country->load($this->order->getFieldData($countryIdFieldName));
                    return oxNew(Recipient::class, $content, $country->getFieldData('oxisoalpha2'));
                }
            } catch (NumberParseException $e) {
                LoggerHandler::getInstance()->getLogger()->info($e->getMessage(), [$content, $country->getFieldData('oxisoalpha2')]);
            } catch (RecipientException $e) {
                LoggerHandler::getInstance()->getLogger()->info($e->getMessage(), [$content, $country->getFieldData('oxisoalpha2')]);
            }
        }

        /** @var noRecipientFoundException $exc */
        $exc = oxNew(noRecipientFoundException::class);
        throw $exc;
    }

    /**
     * @return string[]
     */
    public function getSmsRecipientFields(): array
    {
        $customFields = (oxNew(Configuration::class))->getOrderRecipientFields();

        return count($customFields) ?
            $customFields :
            [
                'oxdelfon'  => 'oxdelcountryid',
                'oxbillfon' => 'oxbillcountryid'
            ];
    }
}
