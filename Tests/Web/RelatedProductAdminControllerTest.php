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
use Symfony\Component\Form\Form;

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
        $this->deleteAllRows(array('plg_related_product'));
    }

    /**
     * test route product edit page.
     */
    public function testRoutingAdminProductRegistration()
    {
        $crawler = $this->client->request('GET', $this->app->url('admin_product_product_new'));

        $this->assertContains('関連商品', $crawler->html());
    }

    /**
     * test create related product.
     */
    public function testCreateRelatedProduct()
    {
        $faker = $this->getFaker();
        $content = $faker->word;
        $childProductId = 1;
        $formData = $this->createFormData($content, $childProductId);

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
     * test create related product with no child product.
     */
    public function testCreateRelatedProductNoChildProduct()
    {
        $faker = $this->getFaker();
        $content = $faker->word;
        $childProductId = null;
        $formData = $this->createFormData($content, $childProductId);

        $this->client->request(
            'POST',
            $this->app->url('admin_product_product_new'),
            array('admin_product' => $formData)
        );

        $RelatedProduct = $this->app['eccube.plugin.repository.related_product']->findOneBy(
            array('content' => $content)
        );

        $this->expected = 0;
        $this->actual = count($RelatedProduct);
        $this->verify();
    }
    /**
     * test create related product with no content.
     */
    public function testCreateRelatedProductNoContent()
    {
        $content = null;
        $childProductId = 1;
        $formData = $this->createFormData($content, $childProductId);

        $this->client->request(
            'POST',
            $this->app->url('admin_product_product_new'),
            array('admin_product' => $formData)
        );

        $ChildProduct = $this->app['eccube.repository.product']->find($childProductId);
        $RelatedProduct = $this->app['eccube.plugin.repository.related_product']->findOneBy(
            array('ChildProduct' => $ChildProduct)
        );

        $this->expected = $childProductId;
        $this->actual = $RelatedProduct->getChildProduct()->getId();
        $this->verify();
    }

    /**
     * test create related product with content over 4000 character.
     */
    public function testCreateRelatedProductNoMaxLengthContent()
    {
        $faker = $this->getFaker();
        $content = $faker->text(9999);
        $childProductId = 1;
        $formData = $this->createFormData($content, $childProductId);

        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_product_product_new'),
            array('admin_product' => $formData)
        );

        $this->assertContains('値が長すぎます。4000文字以内でなければなりません。', $crawler->html());
    }

    /**
     * test related product maximum 5 items.
     */
    public function testRelatedProductMaximum5()
    {
        for ($i = 1; $i < 6; ++$i) {
            $this->initRelatedProduct(2);
        }
        $this->client->request(
            'GET',
            $this->app->url('admin_product_product_edit', array('id' => 2))
        );
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    /**
     * test related product over maximum 5 items.
     */
    public function testRelatedProductOverMaximum5()
    {
        for ($i = 1; $i < 10; ++$i) {
            $this->initRelatedProduct(2);
        }
        $this->client->request(
            'GET',
            $this->app->url('admin_product_product_edit', array('id' => 2))
        );
        $this->assertTrue($this->client->getResponse()->isSuccessful());
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
        $this->assertContains('パーコレーター', $productList);
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
        $this->assertContains('パーコレーター', $productList);
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
        $this->assertContains('パーコレーター', $productList);
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
     * @param string $content
     * @param int    $childId
     *
     * @return Form $form
     */
    public function createFormData($content = null, $childId = 1)
    {
        $faker = $this->getFaker();
        $form = array(
            '_token' => 'dummy',
            'name' => $faker->word,
            'class' => array('product_type' => 1, 'price02' => 50, 'stock_unlimited' => 1),
            'description_detail' => $faker->word,
            'Status' => 1,
            'related_collection' => array(
                0 => array('ChildProduct' => $childId, 'content' => $content),
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
