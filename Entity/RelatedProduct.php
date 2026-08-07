<?php

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

namespace Plugin\RelatedProduct44\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Eccube\Entity\Product;
use Plugin\RelatedProduct44\Repository\RelatedProductRepository;

/**
 * Class RelatedProduct.
 */
#[ORM\Table(name: 'plg_related_product')]
#[ORM\Entity(repositoryClass: RelatedProductRepository::class)]
class RelatedProduct extends AbstractEntity
{
    #[ORM\Column(name: 'id', type: Types::INTEGER, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'content', type: Types::STRING, nullable: true, length: 4000)]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'RelatedProducts')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id')]
    private ?Product $Product = null;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: 'child_product_id', referencedColumnName: 'id')]
    private ?Product $ChildProduct = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * getContent.
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * set related product content.
     *
     * @return $this
     */
    public function setContent(?string $content = null): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * get related product product.
     */
    public function getProduct(): ?Product
    {
        return $this->Product;
    }

    /**
     * set related product product.
     *
     * @return $this
     */
    public function setProduct(Product $Product): self
    {
        $this->Product = $Product;

        return $this;
    }

    /**
     * getChildProduct.
     */
    public function getChildProduct(): ?Product
    {
        return $this->ChildProduct;
    }

    /**
     * setChildProduct.
     *
     * @return $this
     */
    public function setChildProduct(?Product $Product = null): self
    {
        $this->ChildProduct = $Product;

        return $this;
    }
}
