<?php

namespace KTPL\AkeneoTrashBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use KTPL\AkeneoTrashBundle\Manager\AkeneoTrashManager;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The Akeneo trash controller
 *
 * @author    Krishan Kant <krishan.kant@krishtechnolabs.com>
 * @copyright 2021 Krishtechnolabs (https://www.krishtechnolabs.com/)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AkeneoTrashController
{
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var RemoverInterface */
    protected $productRemover;

    /** @var RemoverInterface */
    private $productModelRemover;

    /** @var Client */
    private $productAndProductModelClient;

    /** @var ObjectFilterInterface */
    private $objectFilter;

    /** @var AkeneoTrashManager */
    protected $akeneoTrashManager;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductModelRepositoryInterface $productModelRepository,
        RemoverInterface $productRemover,
        RemoverInterface $productModelRemover,
        Client $productAndProductModelClient,
        ObjectFilterInterface $objectFilter,
        AkeneoTrashManager $akeneoTrashManager
    ) {
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->productRemover = $productRemover;
        $this->productModelRemover = $productModelRemover;
        $this->productAndProductModelClient = $productAndProductModelClient;
        $this->objectFilter = $objectFilter;
        $this->akeneoTrashManager = $akeneoTrashManager;
    }

    /**
     * Remove product
     *
     * @param Request $request
     * @param int     $id
     *
     * @AclAncestor("ktpl_akeneo_trash_remove_product")
     *
     * @return JsonResponse
     */
    public function removeProductAction(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $product = $this->findProductOr404($id);
        $this->productRemover->remove($product);

        $this->productAndProductModelClient->refreshIndex();

        return new JsonResponse();
    }

    /**
     * Remove product model
     *
     * @param Request $request
     * @param int     $id
     *
     * @AclAncestor("ktpl_akeneo_trash_remove_product")
     *
     * @return JsonResponse
     */
    public function removeProductModelAction(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $product = $this->findProductModelOr404($id);
        $this->productModelRemover->remove($product);

        $this->productAndProductModelClient->refreshIndex();

        return new JsonResponse();
    }

    /**
     * Restore item from trash
     *
     * @param Request $request
     * @param string  $resource
     * @param int     $id
     *
     * @AclAncestor("ktpl_akeneo_trash_restore_trash")
     *
     * @return JsonResponse
     */
    public function restoreTrashAction(Request $request, $resource, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        
        $this->akeneoTrashManager->restoreItemFromTrashById([
            'resourceId' => $id,
            'resource' => $resource
        ]);

        return new JsonResponse();
    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param string $id the product id
     *
     * @throws NotFoundHttpException
     *
     * @return ProductInterface
     */
    protected function findProductOr404($id)
    {
        $product = $this->productRepository->find($id);

        if (null === $product) {
            throw new NotFoundHttpException(
                sprintf('Product with id %s could not be found.', $id)
            );
        }

        return $product;
    }

    /**
     * Find a product model by its id or throw a 404
     *
     * @param string $id the product id
     *
     * @throws NotFoundHttpException
     *
     * @return ProductModelInterface
     */
    protected function findProductModelOr404($id): ProductModelInterface
    {
        $productModel = $this->productModelRepository->find($id);
        $productModel = $this->objectFilter->filterObject($productModel, 'pim.internal_api.product.view') ? null : $productModel;

        if (null === $productModel) {
            throw new NotFoundHttpException(
                sprintf('ProductModel with id %s could not be found.', $id)
            );
        }

        return $productModel;
    }
}
