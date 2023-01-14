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
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;
use Monolog\Logger;
use OxidEsales\Eshop\Application\Model\Content;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
use OxidEsales\Eshop\Core\Database\Adapter\Doctrine\Database;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\StandardException;
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
        $qb = $queryBuilderFactory->create();
        $qb->select('column_type')
            ->from('INFORMATION_SCHEMA.COLUMNS')
            ->where(
                $qb->expr()->and(
                    $qb->expr()->eq(
                        'table_schema',
                        $qb->createNamedParameter($this->getConfig()->getConfigParam('dbName'))
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

        $db = $this->getDb();

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
     * @return void
     * @throws DoctrineDriverException
     * @throws DoctrineException
     */
    public function checkCmsItems()
    {
        foreach(
            [
                'd3linkmobilityordercanceled' => 'addCms1Item',
                'd3linkmobilityfinishedorder' => 'addCms2Item',
                'd3linkmobilityordersendednow' => 'addCms3Item'
            ] as $checkIdent => $methodName
        ) {
            if ($this->cmsMissing($checkIdent)) {
                call_user_func([$this, $methodName]);
            }
        }
    }

    /**
     * @param $checkIdent
     * @return bool
     * @throws DoctrineDriverException
     * @throws DoctrineException
     */
    protected function cmsMissing($checkIdent): bool
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getContainer()->get(QueryBuilderFactoryInterface::class)->create();
        $qb->select('count(oxid)')
            ->from(oxNew(Content::class)->getViewName())
            ->where(
                $qb->expr()->and(
                    $qb->expr()->eq(
                        'oxloadid',
                        $qb->createNamedParameter($checkIdent)
                    ),
                    $qb->expr()->eq(
                        'oxshopid',
                        $qb->createNamedParameter($this->getConfig()->getShopId())
                    )
                )
            )
            ->getFirstResult();

        /** @var Statement $statement */
        $statement = $qb->execute();

        return !$statement->fetchOne();
    }

    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected function addCms1Item()
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getContainer()->get(QueryBuilderFactoryInterface::class)->create();
        $qb->insert('oxcontents')
            ->values([
                'oxid'  => 'MD5(CONCAT('.$qb->createNamedParameter(__FUNCTION__).', NOW()))',
                'oxloadid'  => $qb->createNamedParameter('d3linkmobilityordercanceled'),
                'oxshopid'  => $qb->createNamedParameter($this->getConfig()->getShopId()),
                'oxsnippet' => $qb->createNamedParameter(1, ParameterType::INTEGER),
                'oxtype'    => $qb->createNamedParameter(0, ParameterType::INTEGER),
                'oxactive'  => $qb->createNamedParameter(1, ParameterType::INTEGER),
                'oxactive_1'=> $qb->createNamedParameter(1, ParameterType::INTEGER),
                'oxposition'=> $qb->createNamedParameter(''),
                'oxtitle'   => $qb->createNamedParameter('Linkmobility: Bestellung storniert'),
                'oxcontent' => $qb->createNamedParameter('Hallo [{$order->getFieldData(\'oxbillfname\')}] [{$order->getFieldData(\'oxbilllname\')}],'.PHP_EOL.PHP_EOL.'Ihre Bestellung [{$order->getFieldData(\'oxordernr\')}] wurde storniert.'.PHP_EOL.PHP_EOL.'Ihr Team von [{$shop->getFieldData(\'oxname\')}].'),
                'oxtitle_1' => $qb->createNamedParameter('Linkmobility: order canceled'),
                'oxcontent_1'=> $qb->createNamedParameter('Hello [{$order->getFieldData(\'oxbillfname\')}] [{$order->getFieldData(\'oxbilllname\')}],'.PHP_EOL.PHP_EOL.'Your order [{$order->getFieldData(\'oxordernr\')}] has been cancelled.'.PHP_EOL.PHP_EOL.'Your team at [{$shop->getFieldData(\'oxname\')}].'),
                'oxcatid'   => $qb->createNamedParameter('943a9ba3050e78b443c16e043ae60ef3'),
                'oxfolder'  => $qb->createNamedParameter('')
            ]);
        $qb->execute();
    }

    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected function addCms2Item()
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getContainer()->get(QueryBuilderFactoryInterface::class)->create();
        $qb->insert('oxcontents')
            ->values([
                'oxid'  => 'MD5(CONCAT('.$qb->createNamedParameter(__FUNCTION__).', NOW()))',
                'oxloadid'  => $qb->createNamedParameter('d3linkmobilityfinishedorder'),
                'oxshopid'  => $qb->createNamedParameter($this->getConfig()->getShopId()),
                'oxsnippet' => $qb->createNamedParameter(1, ParameterType::INTEGER),
                'oxtype'    => $qb->createNamedParameter(0, ParameterType::INTEGER),
                'oxactive'  => $qb->createNamedParameter(1, ParameterType::INTEGER),
                'oxactive_1'=> $qb->createNamedParameter(1, ParameterType::INTEGER),
                'oxposition'=> $qb->createNamedParameter(''),
                'oxtitle'   => $qb->createNamedParameter('Linkmobility: Bestellung eingegangen'),
                'oxcontent' => $qb->createNamedParameter('Hallo [{$order->getFieldData(\'oxbillfname\')}] [{$order->getFieldData(\'oxbilllname\')}],'.PHP_EOL.PHP_EOL.'vielen Dank für Ihre Bestellung. Wir haben diese unter der Bestellnummer [{$order->getFieldData(\'oxordernr\')}] angelegt und werden diese schnellstmöglich bearbeiten.'.PHP_EOL.PHP_EOL.'Ihr Team von [{$shop->getFieldData(\'oxname\')}].'),
                'oxtitle_1' => $qb->createNamedParameter('Linkmobility: order recieved'),
                'oxcontent_1'=> $qb->createNamedParameter('Hello [{$order->getFieldData(\'oxbillfname\')}] [{$order->getFieldData(\'oxbilllname\')}],'.PHP_EOL.PHP_EOL.'Thank you for your order. We have saved it under the order number [{$order->getFieldData(\'oxordernr\')}] and will process it as soon as possible.'.PHP_EOL.PHP_EOL.'Your team at [{$shop->getFieldData(\'oxname\')}].'),
                'oxcatid'   => $qb->createNamedParameter('943a9ba3050e78b443c16e043ae60ef3'),
                'oxfolder'  => $qb->createNamedParameter('')
            ]);
        $qb->execute();
    }

    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected function addCms3Item()
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getContainer()->get(QueryBuilderFactoryInterface::class)->create();
        $qb->insert('oxcontents')
            ->values([
                'oxid'  => 'MD5(CONCAT('.$qb->createNamedParameter(__FUNCTION__).', NOW()))',
                'oxloadid'  => $qb->createNamedParameter('d3linkmobilityordersendednow'),
                'oxshopid'  => $qb->createNamedParameter($this->getConfig()->getShopId()),
                'oxsnippet' => $qb->createNamedParameter(1, ParameterType::INTEGER),
                'oxtype'    => $qb->createNamedParameter(0, ParameterType::INTEGER),
                'oxactive'  => $qb->createNamedParameter(1, ParameterType::INTEGER),
                'oxactive_1'=> $qb->createNamedParameter(1, ParameterType::INTEGER),
                'oxposition'=> $qb->createNamedParameter(''),
                'oxtitle'   => $qb->createNamedParameter('Linkmobility: Bestellung versendet'),
                'oxcontent' => $qb->createNamedParameter('Hallo [{$order->getFieldData(\'oxbillfname\')}] [{$order->getFieldData(\'oxbilllname\')}],'.PHP_EOL.PHP_EOL.'Ihre Bestellung [{$order->getFieldData(\'oxordernr\')}] wurde eben versendet. [{if $order->getFieldData(\'oxtrackcode\')}]Der Trackingcode dazu ist: [{$order->getFieldData(\'oxtrackcode\')}].[{/if}]'.PHP_EOL.PHP_EOL.'Ihr Team von [{$shop->getFieldData(\'oxname\')}].'),
                'oxtitle_1' => $qb->createNamedParameter('Linkmobility: order shipped'),
                'oxcontent_1'=> $qb->createNamedParameter('Hello [{$order->getFieldData(\'oxbillfname\')}] [{$order->getFieldData(\'oxbilllname\')}],'.PHP_EOL.PHP_EOL.'Your order [{$order->getFieldData(\'oxordernr\')}] has just been shipped. [{if $order->getFieldData(\'oxtrackcode\')}]The tracking code for this is: [{$order->getFieldData(\'oxtrackcode\')}].[{/if}]'.PHP_EOL.PHP_EOL.'Your team at [{$shop->getFieldData(\'oxname\')}].'),
                'oxcatid'   => $qb->createNamedParameter('943a9ba3050e78b443c16e043ae60ef3'),
                'oxfolder'  => $qb->createNamedParameter('')
            ]);
        $qb->execute();
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return ContainerFactory::getInstance()->getContainer();
    }

    /**
     * @return Database
     */
    protected function getDb(): Database
    {
        /** @var Database $db */
        $db = d3GetOxidDIC()->get('d3ox.linkmobility.'.DatabaseInterface::class.'.assoc');

        return $db;
    }

    /**
     * @return Config
     */
    protected function getConfig(): Config
    {
        /** @var Config $config */
        $config = d3GetOxidDIC()->get('d3ox.linkmobility.'.Config::class);

        return $config;
    }
}
