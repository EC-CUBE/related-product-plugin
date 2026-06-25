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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Attribute\EntityExtension;

#[EntityExtension(\Eccube\Entity\Product::class)]
trait ProductTrait
{
    /**
     * @var Collection<int, RelatedProduct>
     */
    #[ORM\OneToMany(targetEntity: RelatedProduct::class, mappedBy: 'Product', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $RelatedProducts;

    /**
     * @return Collection<int, RelatedProduct>
     */
    public function getRelatedProducts()
    {
        if (null === $this->RelatedProducts) {
            $this->RelatedProducts = new ArrayCollection();
        }

        return $this->RelatedProducts;
    }

    public function addRelatedProduct(RelatedProduct $RelatedProduct): void
    {
        if (null === $this->RelatedProducts) {
            $this->RelatedProducts = new ArrayCollection();
        }

        $this->RelatedProducts[] = $RelatedProduct;
    }

    public function removeRelatedProduct(RelatedProduct $RelatedProduct): bool
    {
        if (null === $this->RelatedProducts) {
            $this->RelatedProducts = new ArrayCollection();
        }

        return $this->RelatedProducts->removeElement($RelatedProduct);
    }
}
