<?php

declare(strict_types=1);

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * https://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RelatedProduct44\Tests\Web;

use Eccube\Common\Constant;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\Product;
use Eccube\Entity\ProductCategory;
use Eccube\Repository\Master\ProductStatusRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Plugin\RelatedProduct44\Entity\RelatedProduct;
use Plugin\RelatedProduct44\Repository\RelatedProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RelatedProductAdminControllerTest.
 */
final class RelatedProductAdminControllerTest extends AbstractAdminWebTestCase
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var RelatedProductRepository
     */
    protected $relatedProductRepository;

    /**
     * @var ProductStatusRepository
     */
    protected $productStatusRepository;

    /**
     * @var Product
     */
    protected $Product;

    /**
     * @var ProductCategory
     */
    protected $Category;

    /**
     * call parent setUp.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->deleteAllRows(['plg_related_product']);

        $this->productRepository = $this->entityManager->getRepository(Product::class);
        $this->relatedProductRepository = $this->entityManager->getRepository(RelatedProduct::class);
        $this->productStatusRepository = $this->entityManager->getRepository(ProductStatus::class);

        $this->Product = $this->createProduct('ディナーフォーク');
        $this->Category = $this->Product->getProductCategories()->current();
    }

    /**
     * test route product edit page.
     */
    public function testRoutingAdminProductRegistration(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, $this->generateUrl('admin_product_product_new'));

        $this->assertStringContainsString('関連商品', $crawler->html());
    }

    /**
     * test create related product.
     */
    public function testCreateRelatedProduct(): void
    {
        $faker = $this->getFaker();
        $content = $faker->word;
        $childProductId = 1;
        $formData = $this->createFormData($content, $childProductId);

        $this->client->request(
            Request::METHOD_POST,
            $this->generateUrl('admin_product_product_new'),
            ['admin_product' => $formData]
        );
        $ChildProduct = $this->productRepository->find($childProductId);
        $RelatedProduct = $this->relatedProductRepository->findOneBy([
            'content' => $content,
            'ChildProduct' => $ChildProduct,
        ]);

        $this->expected = $childProductId;
        $this->assertInstanceOf(RelatedProduct::class, $RelatedProduct);
        $this->actual = $RelatedProduct->getChildProduct()->getId();
        $this->verify();
    }

    /**
     * test update related product.
     */
    public function testUpdateRelatedProduct(): void
    {
        $this->initRelatedProduct(2);
        $formData = $this->createFormData();
        $content = $formData['RelatedProducts'][0]['content'];
        $childProductId = $formData['RelatedProducts'][0]['ChildProduct'];

        $this->client->request(
            Request::METHOD_POST,
            $this->generateUrl('admin_product_product_edit', ['id' => 2]),
            ['admin_product' => $formData]
        );

        $ChildProduct = $this->productRepository->find($childProductId);
        $RelatedProduct = $this->relatedProductRepository->findOneBy([
            'content' => $content,
            'ChildProduct' => $ChildProduct,
        ]);

        $this->expected = $content;
        $this->assertInstanceOf(RelatedProduct::class, $RelatedProduct);
        $this->actual = $RelatedProduct->getContent();
        $this->verify();
    }

    /**
     * test create related product with no child product.
     */
    public function testCreateRelatedProductNoChildProduct(): void
    {
        $faker = $this->getFaker();
        $content = $faker->word;
        $childProductId = null;
        $formData = $this->createFormData($content, $childProductId);

        $this->client->request(
            Request::METHOD_POST,
            $this->generateUrl('admin_product_product_new'),
            ['admin_product' => $formData]
        );

        $RelatedProduct = $this->relatedProductRepository->findOneBy(['content' => $content]);

        $this->assertNotInstanceOf(RelatedProduct::class, $RelatedProduct);
    }

    /**
     * test create related product with no content.
     */
    public function testCreateRelatedProductNoContent(): void
    {
        $content = null;
        $childProductId = 1;
        $formData = $this->createFormData($content, $childProductId);

        $this->client->request(
            Request::METHOD_POST,
            $this->generateUrl('admin_product_product_new'),
            ['admin_product' => $formData]
        );

        $ChildProduct = $this->productRepository->find($childProductId);
        $RelatedProduct = $this->relatedProductRepository->findOneBy(['ChildProduct' => $ChildProduct]);

        $this->expected = $childProductId;
        $this->assertInstanceOf(RelatedProduct::class, $RelatedProduct);
        $this->actual = $RelatedProduct->getChildProduct()->getId();
        $this->verify();
    }

    /**
     * test create related product with content over 4000 character.
     */
    public function testCreateRelatedProductNoMaxLengthContent(): void
    {
        $faker = $this->getFaker();
        $content = $faker->text(9999);
        $childProductId = 1;
        $formData = $this->createFormData($content, $childProductId);

        $crawler = $this->client->request(
            Request::METHOD_POST,
            $this->generateUrl('admin_product_product_new'),
            ['admin_product' => $formData]
        );

        $this->assertStringContainsString('長すぎます。この値は4000文字以下で入力してください。', $crawler->html());
    }

    /**
     * test related product maximum 5 items.
     */
    public function testRelatedProductMaximum5(): void
    {
        for ($i = 1; $i < 6; ++$i) {
            $this->initRelatedProduct(2);
        }
        $this->client->request(
            Request::METHOD_GET,
            $this->generateUrl('admin_product_product_edit', ['id' => 2])
        );
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    /**
     * test related product over maximum 5 items.
     */
    public function testRelatedProductOverMaximum5(): void
    {
        for ($i = 1; $i < 10; ++$i) {
            $this->initRelatedProduct(2);
        }
        $this->client->request(
            Request::METHOD_GET,
            $this->generateUrl('admin_product_product_edit', ['id' => 2])
        );
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    /**
     * search with none condition.
     */
    public function testAjaxSearchProductEmpty(): void
    {
        $crawler = $this->client->request(
            Request::METHOD_POST,
            $this->generateUrl('admin_related_product_search', ['id' => '', 'category_id' => '', '_token' => 'dummy']),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $productList = $crawler->html();
        $this->assertStringContainsString($this->Product->getName(), $productList);
    }

    /**
     *  test display public product.
     */
    public function testAjaxSearchPublicProduct(): void
    {
        $ProductStatus = $this->productStatusRepository->find(ProductStatus::DISPLAY_SHOW);
        $Product = $this->productRepository->findOneBy(['name' => $this->Product->getName()]);
        $this->assertInstanceOf(Product::class, $Product);
        $this->assertInstanceOf(ProductStatus::class, $ProductStatus);
        $Product->setStatus($ProductStatus);
        $this->entityManager->persist($Product);
        $this->entityManager->flush();

        $crawler = $this->client->request(
            Request::METHOD_POST,
            $this->generateUrl('admin_related_product_search', ['id' => '', 'category_id' => '', '_token' => 'dummy']),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $productList = $crawler->html();
        $this->assertStringContainsString($this->Product->getName(), $productList);
    }

    /**
     * test display unpublic product.
     */
    public function testAjaxSearchUnpublicProduct(): void
    {
        $ProductStatus = $this->productStatusRepository->find(ProductStatus::DISPLAY_HIDE);
        $Product = $this->productRepository->findOneBy(['name' => $this->Product->getName()]);
        $this->assertInstanceOf(Product::class, $Product);
        $this->assertInstanceOf(ProductStatus::class, $ProductStatus);
        $Product->setStatus($ProductStatus);
        $this->entityManager->persist($Product);
        $this->entityManager->flush();

        $crawler = $this->client->request(
            Request::METHOD_POST,
            $this->generateUrl('admin_related_product_search', ['id' => '', 'category_id' => '', '_token' => 'dummy']),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $productList = $crawler->html();
        $this->assertStringContainsString($this->Product->getName(), $productList);
    }

    /**
     * search product name.
     */
    public function testAjaxSearchProductName(): void
    {
        $crawler = $this->client->request(
            Request::METHOD_POST,
            $this->generateUrl('admin_related_product_search', ['id' => $this->Product->getName(), 'category_id' => '', '_token' => 'dummy']),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $productList = $crawler->html();
        $this->assertStringContainsString($this->Product->getName(), $productList);
    }

    /**
     * search by product code.
     */
    public function testAjaxSearchProductValueCode(): void
    {
        $crawler = $this->client->request(
            Request::METHOD_POST,
            $this->generateUrl('admin_related_product_search', ['id' => $this->Product->getId(), 'category_id' => '', '_token' => 'dummy']),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $productList = $crawler->html();
        $this->assertStringContainsString($this->Product->getName(), $productList);
    }

    /**
     * search by product id.
     */
    public function testAjaxSearchProductValueId(): void
    {
        $crawler = $this->client->request(
            Request::METHOD_POST,
            $this->generateUrl('admin_related_product_search', ['id' => $this->Product->getId(), 'category_id' => '', '_token' => 'dummy']),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $productList = $crawler->html();
        $this->assertStringContainsString($this->Product->getName(), $productList);
    }

    /**
     * search by category.
     */
    public function testAjaxSearchProductCategory(): void
    {
        $crawler = $this->client->request(
            Request::METHOD_POST,
            $this->generateUrl('admin_related_product_search', ['id' => '', 'category_id' => $this->Category->getCategoryId(), '_token' => 'dummy']),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $productList = $crawler->html();
        $this->assertStringContainsString($this->Product->getName(), $productList);
    }

    /**
     * 整数の ID でないカテゴリは 400 を返す (DB の型エラーで 500 にしない).
     */
    #[DataProvider('invalidIdProvider')]
    public function testAjaxSearchProductWithInvalidCategoryId(mixed $categoryId): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->generateUrl('admin_related_product_search'),
            ['id' => '', 'category_id' => $categoryId],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $this->assertSame(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode(), (string) $this->client->getResponse()->getContent());
    }

    /**
     * 範囲外のページ番号は 400 を返す.
     */
    #[DataProvider('invalidPageNoProvider')]
    public function testAjaxSearchProductPageWithInvalidPageNo(string $pageNo): void
    {
        $this->client->request(
            Request::METHOD_GET,
            $this->generateUrl('admin_related_product_search_product_page', ['page_no' => $pageNo]),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $this->assertSame(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode(), (string) $this->client->getResponse()->getContent());
    }

    /**
     * 検索の前にページ番号なしで開いても 1 ページ目を返す.
     */
    public function testAjaxSearchProductWithoutSearchData(): void
    {
        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->generateUrl('admin_related_product_search'),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertStringContainsString($this->Product->getName(), $crawler->html());
    }

    /**
     * セッションに残った以前の検索条件の不正な値は条件から外す (ページ送りを 500 にしない).
     */
    #[DataProvider('invalidIdProvider')]
    public function testAjaxSearchProductPageWithInvalidSearchDataInSession(mixed $invalidValue): void
    {
        $session = $this->createSession($this->client);
        $session->set('eccube.plugin.related_product.product.search', ['id' => '', 'category_id' => $invalidValue]);
        $session->set('eccube.plugin.related_product.product.search.page_no', $invalidValue);
        $session->save();

        foreach ([
            $this->generateUrl('admin_related_product_search'),
            $this->generateUrl('admin_related_product_search_product_page', ['page_no' => 1]),
        ] as $url) {
            $crawler = $this->client->request(
                Request::METHOD_GET,
                $url,
                [],
                [],
                ['HTTP_X-Requested-With' => 'XMLHttpRequest']
            );

            $this->assertTrue($this->client->getResponse()->isSuccessful(), $url);
            $this->assertStringContainsString($this->Product->getName(), $crawler->html(), $url);
        }
    }

    /**
     * @return \Iterator<string, array<mixed>>
     */
    public static function invalidIdProvider(): \Iterator
    {
        yield '文字列' => ['abc'];
        yield '整数の範囲外' => ['99999999999'];
        yield '負の数' => ['-1'];
        yield '配列' => [['1']];
    }

    /**
     * @return \Iterator<string, array<string>>
     */
    public static function invalidPageNoProvider(): \Iterator
    {
        yield '0' => ['0'];
        yield '整数の範囲外' => ['99999999999999999999'];
    }

    /**
     * create form data for save related product.
     *
     * @return array<string, mixed>
     */
    public function createFormData(?string $content = null, ?int $childId = 1): array
    {
        $faker = $this->getFaker();

        $price01 = $faker->randomNumber(5);
        if (mt_rand(0, 1)) {
            $price01 = number_format($price01);
        }

        $price02 = $faker->randomNumber(5);
        if (mt_rand(0, 1)) {
            $price02 = number_format($price02);
        }

        $form = [
            'class' => [
                'sale_type' => 1,
                'price01' => $price01,
                'price02' => $price02,
                'stock' => $faker->randomNumber(3),
                'stock_unlimited' => 0,
                'code' => $faker->word,
                'sale_limit' => null,
                'delivery_duration' => '',
            ],
            'name' => $faker->word,
            'product_image' => [],
            'description_detail' => $faker->realText,
            'description_list' => $faker->paragraph,
            'Category' => [1],
            'Tag' => [1],
            'search_word' => $faker->word,
            'free_area' => $faker->realText,
            'Status' => 1,
            'note' => $faker->realText,
            'tags' => [],
            'images' => [],
            'add_images' => [],
            'delete_images' => [],
            'RelatedProducts' => [
                0 => ['ChildProduct' => $childId, 'content' => $content],
            ],
            Constant::TOKEN_NAME => 'dummy',
        ];

        return $form;
    }

    /**
     * insert related product in DB.
     */
    private function initRelatedProduct(int $id): RelatedProduct
    {
        $fake = $this->getFaker();
        $Product = $this->productRepository->find($id);
        $RelatedProduct = new RelatedProduct();
        $RelatedProduct->setContent($fake->word);
        $RelatedProduct->setProduct($Product);
        $RelatedProduct->setChildProduct($Product);
        $this->entityManager->persist($RelatedProduct);
        $this->entityManager->flush();

        return $RelatedProduct;
    }
}
