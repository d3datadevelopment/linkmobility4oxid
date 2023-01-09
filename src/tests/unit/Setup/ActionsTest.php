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

namespace D3\Linkmobility4OXID\tests\unit\Setup;

use D3\Linkmobility4OXID\Setup\Actions;
use D3\Linkmobility4OXID\tests\unit\LMUnitTestCase;
use D3\TestingTools\Development\CanAccessRestricted;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;
use Monolog\Logger;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
use OxidEsales\Eshop\Core\Database\Adapter\Doctrine\Database;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactory;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use ReflectionException;
use Symfony\Component\DependencyInjection\Container;

class ActionsTest extends LMUnitTestCase
{
    use CanAccessRestricted;

    /**
     * @test
     * @param $hasEnumValue
     * @param $invocationCount
     * @param $throwException
     * @return void
     * @throws ReflectionException
     * @dataProvider canSetupDatabaseDataProvider
     * @covers \D3\Linkmobility4OXID\Setup\Actions::setupDatabase
     */
    public function canSetupDatabase($hasEnumValue, $invocationCount, $throwException)
    {
        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockBuilder(Logger::class)
            ->onlyMethods(['error'])
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock->expects($this->exactly((int) $throwException))->method('error');
        d3GetOxidDIC()->set('d3ox.linkmobility.'.LoggerInterface::class, $loggerMock);

        /** @var UtilsView|MockObject $utilsViewMock */
        $utilsViewMock = $this->getMockBuilder(UtilsView::class)
            ->onlyMethods(['addErrorToDisplay'])
            ->getMock();
        $utilsViewMock->expects($this->exactly((int) $throwException))->method('addErrorToDisplay');
        d3GetOxidDIC()->set('d3ox.linkmobility.'.UtilsView::class, $utilsViewMock);

        /** @var StandardException|MockObject $standardExceptionMock */
        $standardExceptionMock = $this->getMockBuilder(StandardException::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Actions|MockObject $sut */
        $sut = $this->getMockBuilder(Actions::class)
            ->onlyMethods(['hasRemarkTypeEnumValue', 'addRemarkTypeEnumValue'])
            ->getMock();
        $sut->method('hasRemarkTypeEnumValue')->will(
            $throwException ?
                $this->throwException($standardExceptionMock) :
                $this->returnValue($hasEnumValue)
        );
        $sut->expects($invocationCount)->method('addRemarkTypeEnumValue');

        $this->callMethod(
            $sut,
            'setupDatabase'
        );
    }

    /**
     * @return array[]
     */
    public function canSetupDatabaseDataProvider(): array
    {
        return [
            'has enum value'    => [true, $this->never(), false],
            'has no enum value' => [false, $this->once(), false],
            'throws exception'  => [false, $this->never(), true],
        ];
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Setup\Actions::regenerateViews
     */
    public function canRegenerateViews()
    {
        /** @var DbMetaDataHandler|MockObject $dbMetaDataHandlerMock */
        $dbMetaDataHandlerMock = $this->getMockBuilder(DbMetaDataHandler::class)
            ->onlyMethods(['updateViews'])
            ->getMock();
        $dbMetaDataHandlerMock->expects($this->once())->method('updateViews');
        d3GetOxidDIC()->set('d3ox.linkmobility.'.DbMetaDataHandler::class, $dbMetaDataHandlerMock);

        /** @var Actions $sut */
        $sut = d3GetOxidDIC()->get(Actions::class);

        $this->callMethod(
            $sut,
            'regenerateViews'
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @dataProvider canHasRemarkTypeEnumValueDataProvider
     * @covers \D3\Linkmobility4OXID\Setup\Actions::hasRemarkTypeEnumValue
     */
    public function canHasRemarkTypeEnumValue($fieldType, $expectException, $expected)
    {
        /** @var Actions|MockObject $sut */
        $sut = $this->getMockBuilder(Actions::class)
            ->onlyMethods(['getRemarkTypeFieldType'])
            ->getMock();
        $sut->method('getRemarkTypeFieldType')->willReturn($fieldType);

        if ($expectException) {
            $this->expectException(StandardException::class);
        }

        $this->assertSame(
            $expected,
            $this->callMethod(
                $sut,
                'hasRemarkTypeEnumValue'
            )
        );
    }

    /**
     * @return array[]
     */
    public function canHasRemarkTypeEnumValueDataProvider(): array
    {
        return [
            'no enum'               => ['varchar(25)', true, false],
            'is enum, LM missing'   => ['enum(foobar,barfoo)', false, false],
            'is enum, LM exists'    => ['enum(foobar,LINKMOB,barfoo)', false, true]
        ];
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Setup\Actions::getRemarkTypeFieldType()
     */
    public function canGetRemarkTypeFieldType()
    {
        /** @var Statement|MockObject $resultStatementMock */
        $resultStatementMock = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchOne'])
            ->getMock();
        $resultStatementMock->method('fetchOne')->willReturn('returnFixture');

        /** @var ExpressionBuilder|MockObject $expressionBuilderMock */
        $expressionBuilderMock = $this->getMockBuilder(ExpressionBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var QueryBuilder|MockObject $queryBuilderMock */
        $queryBuilderMock = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['execute', 'expr'])
            ->getMock();
        $queryBuilderMock->method('execute')->willReturn($resultStatementMock);
        $queryBuilderMock->method('expr')->willReturn($expressionBuilderMock);

        /** @var QueryBuilderFactory|MockObject $queryBuilderFactoryMock */
        $queryBuilderFactoryMock = $this->getMockBuilder(QueryBuilderFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $queryBuilderFactoryMock->method('create')->willReturn($queryBuilderMock);

        /** @var Container|MockObject $containerMock */
        $containerMock = $this->getMockBuilder(Container::class)
            ->onlyMethods(['get'])
            ->getMock();
        $containerMock->method('get')->willReturn($queryBuilderFactoryMock);

        /** @var Actions|MockObject $sut */
        $sut = $this->getMockBuilder(Actions::class)
            ->onlyMethods(['getContainer'])
            ->getMock();
        $sut->method('getContainer')->willReturn($containerMock);

        $this->assertSame(
            'returnFixture',
            $this->callMethod(
                $sut,
                'getRemarkTypeFieldType'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Setup\Actions::addRemarkTypeEnumValue()
     */
    public function acnAddRemarkTypeEnumValue()
    {
        /** @var Database|MockObject $databaseMock */
        $databaseMock = $this->getMockBuilder(Database::class)
            ->onlyMethods(['execute', 'quoteIdentifier', 'quoteArray', 'quote'])
            ->getMock();
        $databaseMock->expects($this->once())->method('execute')->willReturn(1);
        $databaseMock->method('quoteIdentifier')->willReturn('foo');
        $databaseMock->method('quoteArray')->willReturn('foo');
        $databaseMock->method('quote')->willReturn('foo');
        d3GetOxidDIC()->set('d3ox.linkmobility.'.DatabaseInterface::class.'.assoc', $databaseMock);

        /** @var Actions|MockObject $sut */
        $sut = $this->getMockBuilder(Actions::class)
            ->onlyMethods(['getUniqueFieldTypes'])
            ->getMock();
        $sut->method('getUniqueFieldTypes')->willReturn(['foobar', 'LINKMOB', 'barfoo']);

        $this->callMethod(
            $sut,
            'addRemarkTypeEnumValue'
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Setup\Actions::getUniqueFieldTypes()
     */
    public function canGetUniqueFieldTypes()
    {
        /** @var Actions|MockObject $sut */
        $sut = $this->getMockBuilder(Actions::class)
            ->onlyMethods(['getRemarkTypeFieldType'])
            ->getMock();
        $sut->method('getRemarkTypeFieldType')->willReturn("enum('val1', 'val2')");

        $this->assertSame(
            [
                'val1',
                'val2',
                'LINKMOB'
            ],
            $this->callMethod(
                $sut,
                'getUniqueFieldTypes'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Linkmobility4OXID\Setup\Actions::getContainer()
     */
    public function canGetContainer()
    {
        /** @var Actions $sut */
        $sut = oxNew(Actions::class);

        $this->assertInstanceOf(
            Container::class,
            $this->callMethod(
                $sut,
                'getContainer'
            )
        );
    }
}