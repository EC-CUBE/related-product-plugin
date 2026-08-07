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

use Eccube\Entity\Product;
use Eccube\Repository\ProductRepository;
use Eccube\Tests\Web\AbstractWebTestCase;
use Plugin\RelatedProduct44\Entity\RelatedProduct;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RelatedProductControllerTest.
 */
final class RelatedProductControllerTest extends AbstractWebTestCase
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var Product
     */
    protected $Product;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->entityManager->getRepository(Product::class);
        $this->Product = $this->createProduct('ディナーフォーク');
    }

    /**
     * Test display related product in product detail page.
     */
    public function testShowRelatedProduct(): void
    {
        $this->initRelatedProduct($this->Product->getId());
        $crawler = $this->client->request(Request::METHOD_GET, $this->generateUrl('product_detail', ['id' => $this->Product->getId()]));

        $this->assertStringContainsString('RelatedProduct-product_area', $crawler->html());
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
