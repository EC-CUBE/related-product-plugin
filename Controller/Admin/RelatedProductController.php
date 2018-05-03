<?php
/*
 * This file is part of the Related Product plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RelatedProduct\Controller\Admin;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Eccube\Controller\AbstractController;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class RelatedProductController.
 */
class RelatedProductController extends AbstractController
{
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * RelatedProductController constructor.
     *
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository $productRepository
     * @param PaginatorInterface $paginator
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        PaginatorInterface $paginator
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->paginator = $paginator;
    }

    /**
     * search product modal.
     *
     * @param Request     $request
     * @param int         $page_no
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/related_product/search_product", name="admin_related_product_search")
     * @Route("/%eccube_admin_route%/related_product/search/product/page/{page_no}", name="admin_related_product_search_product_page", requirements={"page_no":"\d+"})
     */
    public function searchProduct(Request $request, $page_no = null)
    {
        if (!$request->isXmlHttpRequest()) {
            return null;
        }

        $pageCount = $this->eccubeConfig['eccube_default_page_count'];
        $session = $this->session;
        if ('POST' === $request->getMethod()) {
            log_info('get search data with parameters ', array('id' => $request->get('id'), 'category_id' => $request->get('category_id')));
            $page_no = 1;
            $searchData = array(
                'id' => $request->get('id'),
            );
            if ($categoryId = $request->get('category_id')) {
                $searchData['category_id'] = $categoryId;
            }
            $session->set('eccube.plugin.related_product.product.search', $searchData);
            $session->set('eccube.plugin.related_product.product.search.page_no', $page_no);
        } else {
            $searchData = (array) $session->get('eccube.plugin.related_product.product.search');
            if (is_null($page_no)) {
                $page_no = intval($session->get('eccube.plugin.related_product.product.search.page_no'));
            } else {
                $session->set('eccube.plugin.related_product.product.search.page_no', $page_no);
            }
        }

        if (!empty($searchData['category_id'])) {
            $searchData['category_id'] = $this->categoryRepository->find($searchData['category_id']);
        }

        $qb = $this->productRepository->getQueryBuilderBySearchDataForAdmin($searchData);

        /** @var \Knp\Component\Pager\Pagination\SlidingPagination $pagination */
        $pagination = $this->paginator->paginate(
            $qb,
            $page_no,
            $pageCount,
            array('wrap-queries' => true)
        );

        return $this->render('RelatedProduct/Resource/template/admin/modal_result.twig', array(
            'pagination' => $pagination,
        ));
    }

    /**
     * get product information.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Method("POST")
     * @Route("/%eccube_admin_route%/related_product/get_product", name="admin_related_product_get_product")
     */
    public function getProduct(Application $app, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return null;
        }

        $productId = $request->get('product_id');
        $index = $request->get('index');
        $Product = $app['eccube.repository.product']->find($productId);

        return $app->render('RelatedProduct/Resource/template/admin/product.twig', array(
            'Product' => $Product,
            'index' => $index,
        ));
    }
}
