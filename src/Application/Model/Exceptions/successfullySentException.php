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

namespace D3\Linkmobility4OXID\Application\Model\Exceptions;

use Exception;
use OxidEsales\Eshop\Core\Registry;
use Throwable;

class successfullySentException extends Exception
{
    /**
     * @param int            $messageCount
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct( $messageCount = 1, $code = 0, Throwable $previous = null )
    {
        $message = sprintf( Registry::getLang()->translateString( 'D3LM_EXC_SMS_SUCC_SENT' ), $messageCount);

        parent::__construct( $message, $code, $previous );
    }
}