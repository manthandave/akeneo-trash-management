<?php

namespace KTPL\AkeneoTrashBundle\Adapter;

use Oro\Bundle\PimDataGridBundle\Extension\MassAction\MassActionDispatcher;
use Oro\Bundle\PimDataGridBundle\Adapter\OroToPimGridFilterAdapter as BaseOroToPimGridFilterAdapter;

class OroToPimGridFilterAdapter extends BaseOroToPimGridFilterAdapter
{
    const PRODUCT_TRASH_GRID_NAME = 'ktpl_akeneo_product_trash-grid';

    /**
     * {@inheritdoc}
     */
    public function adapt(array $parameters)
    {
        if (self::PRODUCT_TRASH_GRID_NAME === $parameters['gridName']) {
            $filters = $this->massActionDispatcher->getRawFilters($parameters);
        } else {
            $filters = parent::adapt($parameters);
        }

        return $filters;
    }
}
