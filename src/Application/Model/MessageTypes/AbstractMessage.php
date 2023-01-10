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

namespace D3\Linkmobility4OXID\Application\Model\MessageTypes;

use D3\LinkmobilityClient\RecipientsList\RecipientsList;
use D3\LinkmobilityClient\RecipientsList\RecipientsListInterface;
use D3\LinkmobilityClient\Response\ResponseInterface;
use D3\LinkmobilityClient\ValueObject\Recipient;
use Exception;
use OxidEsales\Eshop\Application\Model\Remark;

abstract class AbstractMessage
{
    public const REMARK_IDENT = 'LINKMOB';

    /** @var string */
    protected $message;

    /** @var bool */
    protected $removeLineBreaks = true;

    /** @var bool */
    protected $removeMultipleSpaces = true;

    /** @var ResponseInterface */
    protected $response;

    /** @var RecipientsListInterface */
    protected $recipients;

    /**
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->message = $this->sanitizeMessage($message);
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $userId
     * @param string $recipients
     * @param string $message
     *
     * @return void
     * @throws Exception
     */
    protected function setRemark(string $userId, string $recipients, string $message): void
    {
        /** @var Remark $remark */
        $remark = d3GetOxidDIC()->get('d3ox.linkmobility.'.Remark::class);
        $remark->assign([
            'oxtype'     => self::REMARK_IDENT,
            'oxparentid' => $userId,
            'oxtext'     => $this->getTypeName().' -> '.$recipients.PHP_EOL.$message
        ]);
        $remark->save();
    }

    /**
     * @return ResponseInterface|null
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param RecipientsListInterface $recipients
     * @return void
     */
    protected function setRecipients(RecipientsListInterface $recipients)
    {
        $this->recipients = $recipients;
    }

    /**
     * @return string
     */
    public function getRecipientsList(): string
    {
        $list = [];
        /** @var Recipient $recipient */
        foreach ($this->recipients->getRecipientsList() as $recipient) {
            $list[] = $recipient->get();
        }

        return implode(', ', $list);
    }

    /**
     * @param string $message
     *
     * @return string
     */
    protected function sanitizeMessage(string $message): string
    {
        $message = trim(strip_tags($message));
        $message = $this->removeLineBreaks ? str_replace(["\r", "\n"], ' ', $message) : $message;
        $regexp = '/[^\S\r\n]{2,}/m';
        return $this->removeMultipleSpaces ? (string) preg_replace($regexp, ' ', $message) : $message;
    }

    abstract public function getTypeName(): string;
}
