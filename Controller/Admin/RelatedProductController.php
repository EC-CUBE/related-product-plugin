<?php

namespace Plugin\RelatedProduct\Controller\Admin;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;

class RelatedProductController
{
    public function searchProduct(Application $app, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $searchData = array(
                'id' => $request->get('id'),
            );
            if ($categoryId = $request->get('category_id')) {
                $Category = $app['eccube.repository.category']->find($categoryId);
                $searchData['category_id'] = $Category;
            }

            /** @var $Products \Eccube\Entity\Product[] */
            $Products = $app['eccube.repository.product']
                ->getQueryBuilderBySearchData($searchData)
                ->getQuery()
                ->getResult();

            return $app->renderView(
                'RelatedProduct/Resource/template/Admin/modal_result.twig',
                array(
                    'Products' => $Products,
                )
            );

        }
    }
}