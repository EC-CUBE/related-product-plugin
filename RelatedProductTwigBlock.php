<?php
namespace Plugin\RelatedProduct;

use Eccube\Common\EccubeTwigBlock;

class RelatedProductTwigBlock implements EccubeTwigBlock
{
    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public static function getTwigBlock()
    {
        return [
            '@RelatedProduct/front/block_related_product.twig'
        ];
    }

}
