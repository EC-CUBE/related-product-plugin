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

/**
 * Class RelatedProductController.
 */
class RelatedProductController
{
    /**
     * search product modal.
     *
     * @param Application $app
     * @param Request     $request
     * @param int         $page_no
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchProduct(Application $app, Request $request, $page_no = null)
    {
        if (!$request->isXmlHttpRequest()) {
            return null;
        }

        $pageCount = $app['config']['default_page_count'];
        $session = $app['session'];
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
            $searchData['category_id'] = $app['eccube.repository.category']->find($searchData['category_id']);
        }

        $qb = $app['eccube.repository.product']->getQueryBuilderBySearchDataForAdmin($searchData);

        /** @var \Knp\Component\Pager\Pagination\SlidingPagination $pagination */
        $pagination = $app['paginator']()->paginate(
            $qb,
            $page_no,
            $pageCount,
            array('wrap-queries' => true)
        );

        $paths = array();
        $paths[] = $app['config']['template_admin_realdir'];
        $app['twig.loader']->addLoader(new \Twig_Loader_Filesystem($paths));

        return $app->render('RelatedProduct/Resource/template/admin/modal_result.twig', array(
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
