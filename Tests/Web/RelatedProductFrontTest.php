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

namespace Plugin\RelatedProduct\Tests\Web;


use Eccube\Tests\Web\AbstractWebTestCase;
use Plugin\RelatedProduct\Entity\RelatedProduct;

class RelatedProductFrontTest extends AbstractWebTestCase
{
    protected function createRelatedProduct(\Eccube\Entity\Product $Product)
    {
        $ChildProduct = $this->app['eccube.repository.product']->find(1);
        $RelatedProduct = new RelatedProduct();
        $RelatedProduct->setProductId($Product->getId())
            ->setProduct($Product)
            ->setChildProductId($ChildProduct->getId())
            ->setChildProduct($ChildProduct)
            ->setContent('This is a test');
        $this->app['orm.em']->persist($RelatedProduct);
        $this->app['orm.em']->flush();

        return $RelatedProduct;
    }

    public function testProductDetail()
    {
        $this->deleteAllRows(array('plg_related_product'));
        $Product = $this->createProduct();
        $this->createRelatedProduct($Product);
        $crawler = $this->client->request('GET',
            $this->app->url('product_detail', array('id' => $Product->getId()))
        );
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $this->assertContains('関連商品', $crawler->html());
    }
}