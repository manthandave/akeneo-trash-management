<?php
declare(strict_types=1);

namespace KTPL\AkeneoTrashBundle\EventListener;

use KTPL\AkeneoTrashBundle\Manager\AkeneoTrashManager;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductAndProductModelDatasource;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

/**
 * Remove trash products from product grid
 */
class ExcludeTrashProductsFromGridListener
{
    const PRODUCT_RESOURCE_NAME = 'product';

    const PRODUCT_MODEL_RESOURCE_NAME = 'product_model';

    /** @var AkeneoTrashManager */
    protected $akeneoTrashManager;

    public function __construct(AkeneoTrashManager $akeneoTrashManager)
    {
        $this->akeneoTrashManager = $akeneoTrashManager;
    }

    public function onBuildAfter(BuildAfter $event): void
    {
        $dataSource = $event->getDatagrid()->getDatasource();
        if ($dataSource instanceof ProductDatasource || $dataSource instanceof ProductAndProductModelDatasource) {
            $productCodeTobeExcludeFromGrid = $this->akeneoTrashManager->getTrashResourcesCode(
                [
                    self::PRODUCT_RESOURCE_NAME
                ]
            );
            $productModelCodeTobeExcludeFromGrid = $this->akeneoTrashManager->getTrashResourcesCode(
                [
                    self::PRODUCT_MODEL_RESOURCE_NAME
                ]
            );

            $qb = $dataSource->getQueryBuilder();
            $clause = [
                'terms' => [
                    'identifier' => array_merge($productCodeTobeExcludeFromGrid, $productModelCodeTobeExcludeFromGrid)
                ],
            ];

            $qb->addMustNot($clause);
            
            $clause = [
                'terms' => [
                    'parent' => $productModelCodeTobeExcludeFromGrid,
                ],
            ];
            $qb->addMustNot($clause);
        }
    }
}
