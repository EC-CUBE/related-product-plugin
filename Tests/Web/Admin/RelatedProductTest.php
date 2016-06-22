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


class RelatedProductTest extends RelatedProductCommon
{

    public function testNewProductRender()
    {
        $crawler = $this->client->request('GET', $this->app->url('admin_product_product_new'));

        $this->assertContains('関連商品',$crawler->html());
    }

    public function testNewProduct()
    {
        $this->deleteAllRows(array('plg_related_product'));
        $this->createProduct();
        $productForm = $this->createProductFormData();
        $this->client->request('POST',
            $this->app->url('admin_product_product_new'),
            array('admin_product' => $productForm)
            );
        $Product = $this->app['eccube.repository.product']->find($productForm['related_collection'][0]['ChildProduct']);
        $RelatedProduct = $this->app['eccube.plugin.repository.related_product']->findOneBy(array('ChildProduct' => $Product));
        $this->actual = $RelatedProduct->getContent();
        $this->expected = $productForm['related_collection'][0]['content'];
        $this->verify();
    }

    public function testEditProductRender()
    {
        $this->deleteAllRows(array('plg_related_product'));
        $Product = $this->createProduct();
        $crawler = $this->client->request('GET', $this->app->url('admin_product_product_edit', array('id' => $Product->getId())));

        $this->assertContains('関連商品',$crawler->html());
    }

    public function testEditProduct()
    {
        $this->deleteAllRows(array('plg_related_product'));
        $Product = $this->createProduct();
        $productForm = $this->createProductFormData();
        $this->client->request('POST',
            $this->app->url('admin_product_product_edit', array('id' => $Product->getId())),
            array('admin_product' => $productForm)
        );
        $RelatedProduct = $this->app['eccube.plugin.repository.related_product']->findOneBy(array('Product' => $Product));
        $this->actual = $RelatedProduct->getContent();
        $this->expected = $productForm['related_collection'][0]['content'];
        $this->verify();
    }
}