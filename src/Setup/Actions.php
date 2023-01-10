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
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\Exception as DoctrineException;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;
use Monolog\Logger;
use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
use OxidEsales\Eshop\Core\Database\Adapter\Doctrine\Database;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

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
        } catch (StandardException|DoctrineDriverException|DoctrineException $e) {
            /** @var Logger $logger */
            $logger = d3GetOxidDIC()->get('d3ox.linkmobility.'.LoggerInterface::class);
            $logger->error($e->getMessage());
            /** @var UtilsView $utilsView */
            $utilsView = d3GetOxidDIC()->get('d3ox.linkmobility.'.UtilsView::class);
            $utilsView->addErrorToDisplay($e->getMessage());
        }
    }



    /**
     * Regenerate views for changed tables
     */
    public function regenerateViews(): void
    {
        /** @var DbMetaDataHandler $oDbMetaDataHandler */
        $oDbMetaDataHandler = d3GetOxidDIC()->get('d3ox.linkmobility.'.DbMetaDataHandler::class);
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
            throw oxNew(StandardException::class, 'remark type field has not the expected enum type');
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
        /** @var QueryBuilderFactory $queryBuilderFactory */
        $queryBuilderFactory = $this->getContainer()->get(QueryBuilderFactoryInterface::class);
        /** @var QueryBuilder $qb */
        $qb = $queryBuilderFactory->create();
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

        /** @var Statement $statement */
        $statement = $qb->execute();

        return (string) $statement->fetchOne();
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
        $items = $this->getUniqueFieldTypes();

        /** @var Database $db */
        $db = d3GetOxidDIC()->get('d3ox.linkmobility.'.DatabaseInterface::class.'.assoc');

        $query = 'ALTER TABLE '.$db->quoteIdentifier('oxremark').
            ' CHANGE '.$db->quoteIdentifier('OXTYPE'). ' '.$db->quoteIdentifier('OXTYPE') .
            ' enum('.implode(',', $db->quoteArray($items)).')'.
            ' COLLATE '.$db->quote('utf8_general_ci').' NOT NULL DEFAULT '.$db->quote('r');

        $db->execute($query);
    }

    /**
     * @return string[]
     * @throws DoctrineDriverException
     * @throws DoctrineException
     */
    protected function getUniqueFieldTypes(): array
    {
        $valuePattern = '/(?<=enum\().*(?=\))/i';
        preg_match($valuePattern, $this->getRemarkTypeFieldType(), $matches);

        return array_unique(
            array_merge(
                str_getcsv($matches[0], ',', "'"),
                [AbstractMessage::REMARK_IDENT]
            )
        );
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return ContainerFactory::getInstance()->getContainer();
    }
}