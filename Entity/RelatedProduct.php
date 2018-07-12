<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RelatedProduct\Entity;

use Eccube\Entity\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class RelatedProduct.
 *
 * @ORM\Table(name="plg_related_product")
 * @ORM\Entity(repositoryClass="Plugin\RelatedProduct\Repository\RelatedProductRepository")
 */
class RelatedProduct
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="string", nullable=true)
     */
    private $content;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Product", inversedBy="RelatedProduct", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     * })
     */
    private $Product;

    /**
     * @var int
     */
    private $productId;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Product", inversedBy="RelatedProduct", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="child_product_id", referencedColumnName="id")
     * })
     */
    private $ChildProduct;

    /**
     * @var int
     */
    private $childProductId;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * getContent.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * set related product content.
     *
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * get related product content.
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->Product;
    }

    /**
     * set related product product.
     *
     * @param Product $Product
     *
     * @return $this
     */
    public function setProduct(Product $Product)
    {
        $this->Product = $Product;

        return $this;
    }

    /**
     * get product id.
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * set product id.
     *
     * @param int $productId
     *
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * getChildProduct.
     *
     * @return Product
     */
    public function getChildProduct()
    {
        return $this->ChildProduct;
    }

    /**
     * setChildProduct.
     *
     * @param Product|null $Product
     *
     * @return $this
     */
    public function setChildProduct(Product $Product = null)
    {
        $this->ChildProduct = $Product;

        return $this;
    }

    /**
     * getChildProductId.
     *
     * @return int
     */
    public function getChildProductId()
    {
        return $this->childProductId;
    }

    /**
     * setChildProductId.
     *
     * @param int $productId
     *
     * @return $this
     */
    public function setChildProductId($productId)
    {
        $this->childProductId = $productId;

        return $this;
    }
}
