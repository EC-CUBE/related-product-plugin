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

namespace Plugin\RelatedProduct\Form\Extension\Admin;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Plugin\RelatedProduct\Form\Type\Admin\RelatedProductType;
use Eccube\Form\Type\Admin\ProductType;

/**
 * Class RelatedCollectionExtension.
 */
class RelatedCollectionExtension extends AbstractTypeExtension
{
    /**
     * RelatedCollectionExtension.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('related_collection', CollectionType::class, [
                'label' => 'related_product.block.title',
                'entry_type' => RelatedProductType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'mapped' => false,
            ])
        ;
    }

    /**
     * product admin form name.
     *
     * @return string
     */
    public function getExtendedType()
    {
        return ProductType::class;
    }
}
