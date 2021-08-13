<?php

namespace KTPL\AkeneoTrashBundle\Adapter;

use Oro\Bundle\PimDataGridBundle\Extension\MassAction\MassActionDispatcher;
use Oro\Bundle\PimDataGridBundle\Adapter\OroToPimGridFilterAdapter as BaseOroToPimGridFilterAdapter;

/**
 * Trash grid filter adapter
 *
 * @author    Krishan Kant <krishan.kant@krishtechnolabs.com>
 * @copyright 2021 Krishtechnolabs (https://www.krishtechnolabs.com/)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
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
