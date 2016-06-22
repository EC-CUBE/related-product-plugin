<?php
/**
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RelatedProduct\Tests\Web\Admin;


class RelatedProductSearchTest extends RelatedProductCommon
{
    public function testSearchProduct()
    {
        $productId = 1;
        $Product = $this->app['eccube.repository.product']->find($productId);
        $form = array(
            '_token' => 'dummy',
            'id' => $productId,
            'category_id' => 5
        );
        $crawler = $this->client->request('POST', $this->app->url('admin_related_product_search'),
            array('admin_related_product' => $form),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertContains($Product->getName(), $crawler->html());
    }
}