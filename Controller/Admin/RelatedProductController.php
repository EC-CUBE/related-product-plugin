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
                ->getQueryBuilderBySearchDataForAdmin($searchData)
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