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

class RelatedProduct
{
    private $id;

    private $content;

    private $Product;

    private $productId;

    private $ChildProduct;

    private $childProdcutId;

    public function getId()
    {
        return $this->id;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function getProduct()
    {
        return $this->Product;
    }

    public function setProduct(\Eccube\Entity\Product $Product)
    {
        $this->Product = $Product;

        return $this;
    }

    public function getProductId()
    {
        return $this->productId;
    }

    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    public function getChildProduct()
    {
        return $this->ChildProduct;
    }

    public function setChildProduct(\Eccube\Entity\Product $Product = null)
    {
        $this->ChildProduct = $Product;

        return $this;
    }

    public function getChildProductId()
    {
        return $this->childProdcutId;
    }

    public function setChildProductId($productId)
    {
        $this->childProdcutId = $productId;

        return $this;
    }
}