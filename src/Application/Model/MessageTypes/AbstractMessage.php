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

use D3\LinkmobilityClient\Response\ResponseInterface;
use D3\LinkmobilityClient\ValueObject\Recipient;
use Exception;
use OxidEsales\Eshop\Application\Model\Remark;

abstract class AbstractMessage
{
    const REMARK_IDENT = 'LINKMOB';

    protected $message;
    protected $removeLineBreaks = true;
    protected $removeMultipleSpaces = true;

    protected $response;
    protected $recipients = [];

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
     * @param $userId
     * @param $recipients
     * @param $message
     *
     * @throws Exception
     */
    protected function setRemark($userId, $recipients, $message)
    {
        $remark = oxNew(Remark::class);
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
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param array $recipients
     * @return void
     */
    protected function setRecipients(array $recipients)
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
        foreach ($this->recipients as $recipient) {
            $list[] = $recipient->get();
        }

        return implode(', ', $list);
    }

    /**
     * @param $message
     *
     * @return string
     */
    protected function sanitizeMessage($message): string
    {
        $message = trim(strip_tags($message));
        $message = $this->removeLineBreaks ? str_replace(["\r", "\n"], ' ', $message) : $message;
        $regexp = '/\s{2,}/m';
        return $this->removeMultipleSpaces ? preg_replace($regexp, ' ', $message) : $message;
    }

    abstract public function getTypeName() : string;
}
