<?php
/*
* This file is part of EC-CUBE
*
* Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
* http://www.lockon.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\RelatedProduct\Controller\Admin;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;

/**
 * Class RelatedProductController
 * @package Plugin\RelatedProduct\Controller\Admin
 */
class RelatedProductController
{
    /**
     * @param Application $app
     * @param Request $request
     * @param integer $page_no
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
            $page_no = 1;
            $searchData = array(
                'id' => $request->get('id'),
            );
            if ($categoryId = $request->get('category_id')) {
                $Category = $app['eccube.repository.category']->find($categoryId);
                $searchData['category_id'] = $Category;
            }
            $session->set('eccube.plugin.related_product.product.search', $searchData);
            $session->set('eccube.plugin.related_product.product.search.page_no', $page_no);
        } else {
            $searchData = (array)$session->get('eccube.plugin.related_product.product.search');
            if (is_null($page_no)) {
                $page_no = intval($session->get('eccube.plugin.related_product.product.search.page_no'));
            } else {
                $session->set('eccube.plugin.related_product.product.search.page_no', $page_no);
            }
        }

        $qb = $app['eccube.repository.product']->getQueryBuilderBySearchDataForAdmin($searchData);

        /** @var \Knp\Component\Pager\Pagination\SlidingPagination $pagination */
        $pagination = $app['paginator']()->paginate(
            $qb,
            $page_no,
            $pageCount,
            array('wrap-queries' => true)
        );
        /** @var ArrayCollection */
        $arrProduct = $pagination->getItems();

        $paths = array();
        $paths[] = $app['config']['template_admin_realdir'];
        $app['twig.loader']->addLoader(new \Twig_Loader_Filesystem($paths));

        return $app->render('RelatedProduct/Resource/template/admin/modal_result.twig', array(
            'pagination' => $pagination,
        ));
    }
}