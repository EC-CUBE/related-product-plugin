<?php
/*
 * This file is part of the Related Product plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RelatedProduct\Entity;

use Eccube\Entity\Product;

/**
 * Class RelatedProduct.
 */
class RelatedProduct
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $content;

    /**
     * @var Product
     */
    private $Product;

    /**
     * @var int
     */
    private $productId;

    /**
     * @var Product
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
