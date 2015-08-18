<?php

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