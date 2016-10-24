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

use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;
use Plugin\RelatedProduct\Entity\RelatedProduct;

/**
 * Class RelatedProductAdminControllerTest.
 */
class RelatedProductAdminControllerTest extends AbstractAdminWebTestCase
{
    /**
     * call parent setUp.
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * test route product edit page.
     */
    public function testRoutingAdminProductRegistration()
    {
        $crawler = $this->client->request('GET',
            $this->app->url('admin_product_product_new')
        );

        $this->assertContains('関連商品', $crawler->html());
    }

    /**
     * test create related product.
     */
    public function testCreateRelatedProduct()
    {
        $formData = $this->createFormData();
        $content = $formData['related_collection'][0]['content'];
        $childProductId = $formData['related_collection'][0]['ChildProduct'];

        $this->client->request(
            'POST',
            $this->app->url('admin_product_product_new'),
            array('admin_product' => $formData)
        );

        $ChildProduct = $this->app['eccube.repository.product']->find($childProductId);
        $RelatedProduct = $this->app['eccube.plugin.repository.related_product']->findOneBy(
            array('content' => $content, 'ChildProduct' => $ChildProduct)
        );

        $this->expected = $childProductId;
        $this->actual = $RelatedProduct->getChildProduct()->getId();
        $this->verify();
    }

    /**
     * test update related product.
     */
    public function testUpdateRelatedProduct()
    {
        $this->initRelatedProduct(2);
        $formData = $this->createFormData();
        $content = $formData['related_collection'][0]['content'];
        $childProductId = $formData['related_collection'][0]['ChildProduct'];

        $this->client->request(
            'POST',
            $this->app->url('admin_product_product_edit', array('id' => 2)),
            array('admin_product' => $formData)
        );

        $ChildProduct = $this->app['eccube.repository.product']->find($childProductId);
        $RelatedProduct = $this->app['eccube.plugin.repository.related_product']->findOneBy(
            array('content' => $content, 'ChildProduct' => $ChildProduct)
        );

        $this->expected = $content;
        $this->actual = $RelatedProduct->getContent();
        $this->verify();
    }

    /**
     * search with none condition.
     */
    public function testAjaxSearchProductEmpty()
    {
        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_related_product_search', array('id' => '', 'category_id' => '', '_token' => 'dummy')),
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $productList = $crawler->html();
        $this->assertContains('ディナーフォーク', $productList);
    }

    /**
     *  test display public product.
     */
    public function testAjaxSearchPublicProduct()
    {
        $Disp = $this->app['orm.em']->getRepository('Eccube\Entity\Master\Disp')->find(1);
        $Product = $this->app['eccube.repository.product']->findOneBy(array('name' => 'ディナーフォーク'));
        $Product->setStatus($Disp);
        $this->app['orm.em']->persist($Product);
        $this->app['orm.em']->flush($Product);

        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_related_product_search', array('id' => '', 'category_id' => '', '_token' => 'dummy')),
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $productList = $crawler->html();
        $this->assertContains('ディナーフォーク', $productList);
    }

    /**
     * test display unpublic product.
     */
    public function testAjaxSearchUnpublicProduct()
    {
        $Disp = $this->app['orm.em']->getRepository('Eccube\Entity\Master\Disp')->find(2);
        $Product = $this->app['eccube.repository.product']->findOneBy(array('name' => 'ディナーフォーク'));
        $Product->setStatus($Disp);
        $this->app['orm.em']->persist($Product);
        $this->app['orm.em']->flush($Product);

        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_related_product_search', array('id' => '', 'category_id' => '', '_token' => 'dummy')),
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $productList = $crawler->html();
        $this->assertContains('ディナーフォーク', $productList);
    }

    /**
     * search product name.
     */
    public function testAjaxSearchProductName()
    {
        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_related_product_search', array('id' => 'パーコレーター', 'category_id' => 1, '_token' => 'dummy')),
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $productList = $crawler->html();
        $this->assertContains('パーコレーター', $productList);
    }

    /**
     * search by product code.
     */
    public function testAjaxSearchProductValueCode()
    {
        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_related_product_search', array('id' => 'cafe-01', 'category_id' => '', '_token' => 'dummy')),
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $productList = $crawler->html();
        $this->assertContains('パーコレーター', $productList);
    }

    /**
     * search by product id.
     */
    public function testAjaxSearchProductValueId()
    {
        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_related_product_search', array('id' => 1, 'category_id' => '', '_token' => 'dummy')),
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $productList = $crawler->html();
        $this->assertContains('パーコレーター', $productList);
    }

    /**
     * search by category.
     */
    public function testAjaxSearchProductCategory()
    {
        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_related_product_search', array('id' => '', 'category_id' => 6, '_token' => 'dummy')),
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $productList = $crawler->html();
        $this->assertContains('ディナーフォーク', $productList);
        $this->assertContains('パーコレーター', $productList);
    }

    /**
     * create form data for save related product.
     *
     * @return $form
     */
    public function createFormData()
    {
        $faker = $this->getFaker();
        $form = array(
            '_token' => 'dummy',
            'name' => $faker->word,
            'class' => array('product_type' => 1, 'price02' => 50, 'stock_unlimited' => 1),
            'description_detail' => $faker->word,
            'Status' => 1,
            'related_collection' => array(
                0 => array('ChildProduct' => 1, 'content' => $faker->word),
            ),
        );

        return $form;
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
