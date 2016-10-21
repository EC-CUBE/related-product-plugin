<?php
/*
* This file is part of EC-CUBE
*
* Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
* http://www.lockon.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\RelatedProduct\Entity;

use Eccube\Entity\Product;

/**
 * Class RelatedProduct
 * @package Plugin\RelatedProduct\Entity
 */
class RelatedProduct
{

    private $id;

    private $content;

    private $Product;

    private $productId;

    private $ChildProduct;

    private $childProdcutId;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * set related product content
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * get related product content
     * @return $Product
     */
    public function getProduct()
    {
        return $this->Product;
    }

    /**
     * set related product product
     * @param \Eccube\Entity\Product $Product
     * @return $this
     */
    public function setProduct(Product $Product)
    {
        $this->Product = $Product;

        return $this;
    }

    /**
     * get product id
     * @return integer
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * set product id
     * @param $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * getChildProduct
     * @return Product
     */
    public function getChildProduct()
    {
        return $this->ChildProduct;
    }

    /**
     * setChildProduct
     * @param \Eccube\Entity\Product|null $Product
     * @return $this
     */
    public function setChildProduct(Product $Product = null)
    {
        $this->ChildProduct = $Product;

        return $this;
    }

    /**
     * getChildProductId
     * @return integer
     */
    public function getChildProductId()
    {
        return $this->childProdcutId;
    }

    /**
     * setChildProductId
     * @param $productId
     * @return $this
     */
    public function setChildProductId($productId)
    {
        $this->childProdcutId = $productId;

        return $this;
    }
}