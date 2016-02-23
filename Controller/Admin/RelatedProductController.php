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

            // 表示されている商品は検索結果に含めない
            $productId = $request->get('product_id');
            $ProductsData = array();
            $count = count($Products);
            $i = 0;
            for($i = 0; $i < $count; $i++) {
                $Product = $Products[$i];
                if ($Product->getId() != $productId) {
                    $ProductsData[] = $Product;
                }
                if ($i >= 10) {
                    break;
                }
            }

            $message = '';
            if ($count > $i) {
                $message = '検索結果の上限を超えています。検索条件を設定してください。';
            }

            return $app->renderView(
                'RelatedProduct/Resource/template/Admin/modal_result.twig',
                array(
                    'Products' => $ProductsData,
                    'message' => $message,
                )
            );

        }
    }
}