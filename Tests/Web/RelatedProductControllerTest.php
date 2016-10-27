<?php
/*
 * This file is part of the Related Product plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RelatedProduct\Tests\Web;

use Eccube\Tests\Web\AbstractWebTestCase;
use Plugin\RelatedProduct\Entity\RelatedProduct;

/**
 * Class RelatedProductControllerTest.
 */
class RelatedProductControllerTest extends AbstractWebTestCase
{
    /**
     * Test display related product in product detail page.
     */
    public function testShowRelatedProduct()
    {
        $this->initRelatedProduct(2);
        $crawler = $this->client->request('GET', $this->app->url('product_detail', array('id' => 2)));

        $this->assertContains('関連商品', $crawler->html());
    }

    /**
     * insert related product in DB.
     *
     * @param $id
     *
     * @return RelatedProduct
     */
    private function initRelatedProduct($id)
    {
        $fake = $this->getFaker();
        $Product = $this->app['eccube.repository.product']->find($id);
        $RelatedProduct = new RelatedProduct();
        $RelatedProduct->setContent($fake->word);
        $RelatedProduct->setProduct($Product);
        $RelatedProduct->setChildProduct($Product);
        $this->app['orm.em']->persist($RelatedProduct);
        $this->app['orm.em']->flush($RelatedProduct);

        return $RelatedProduct;
    }
}
