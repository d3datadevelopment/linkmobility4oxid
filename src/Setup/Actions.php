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

namespace D3\Linkmobility4OXID\Setup;

use D3\Linkmobility4OXID\Application\Model\MessageTypes\AbstractMessage;
use Doctrine\DBAL\Driver\Exception as DoctrineDriverException;
use Doctrine\DBAL\Exception as DoctrineException;
use Doctrine\DBAL\Query\QueryBuilder;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\DatabaseException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Actions
{
    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function setupDatabase()
    {
        try {
            if (!$this->hasRemarkTypeEnumValue()) {
                $this->addRemarkTypeEnumValue();
            }
        } catch (DatabaseException|DoctrineDriverException|DoctrineException $e) {
            Registry::getLogger()->error($e->getMessage());
            Registry::getUtilsView()->addErrorToDisplay($e);
        }
    }



    /**
     * Regenerate views for changed tables
     */
    public function regenerateViews()
    {
        $oDbMetaDataHandler = oxNew(DbMetaDataHandler::class);
        $oDbMetaDataHandler->updateViews();
    }

    /**
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws DoctrineDriverException
     * @throws DoctrineException
     * @throws NotFoundExceptionInterface
     */
    protected function hasRemarkTypeEnumValue(): bool
    {
        $fieldType = $this->getRemarkTypeFieldType();

        $patternEnumCheck = '/^\b(enum)\b/mi';
        if (!preg_match($patternEnumCheck, $fieldType)) {
            throw oxNew(DatabaseException::class, 'remark type field has not the expected enum type');
        }

        $patternValueCheck = '/\b('.preg_quote(AbstractMessage::REMARK_IDENT).')\b/mi';

        return (bool) preg_match($patternValueCheck, $fieldType);
    }

    /**
     * @return string
     * @throws DoctrineDriverException
     * @throws DoctrineException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getRemarkTypeFieldType(): string
    {
        /** @var QueryBuilder $qb */
        $qb = ContainerFactory::getInstance()->getContainer()->get(QueryBuilderFactoryInterface::class)->create();
        $qb->select('column_type')
            ->from('INFORMATION_SCHEMA.COLUMNS')
            ->where(
                $qb->expr()->and(
                    $qb->expr()->eq(
                        'table_schema',
                        $qb->createNamedParameter(Registry::getConfig()->getConfigParam('dbName'))
                    ),
                    $qb->expr()->eq(
                        'table_name',
                        $qb->createNamedParameter('oxremark')
                    ),
                    $qb->expr()->eq(
                        'COLUMN_NAME',
                        $qb->createNamedParameter('oxtype')
                    )
                )
            );

        return (string) $qb->execute()->fetchOne();
    }

    /**
     * @return void
     * @throws DoctrineDriverException
     * @throws DoctrineException
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function addRemarkTypeEnumValue()
    {
        $valuePattern = '/(?<=enum\().*(?=\))/i';
        preg_match($valuePattern, $this->getRemarkTypeFieldType(), $matches);

        $items = array_unique(
            array_merge(
                str_getcsv($matches[0], ',', "'"),
                [AbstractMessage::REMARK_IDENT]
            )
        );

        $db = DatabaseProvider::getDb();

        $query = 'ALTER TABLE '.$db->quoteIdentifier('oxremark').
            ' CHANGE '.$db->quoteIdentifier('OXTYPE'). ' '.$db->quoteIdentifier('OXTYPE') .
            ' enum('.implode(',', $db->quoteArray($items)).')'.
            ' COLLATE '.$db->quote('utf8_general_ci').' NOT NULL DEFAULT '.$db->quote('r');

        $db->execute($query);
    }
}