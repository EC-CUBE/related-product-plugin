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

namespace Plugin\RelatedProduct\Form\Extension\Admin;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class RelatedCollectionExtension
 * @package Plugin\RelatedProduct\Form\Extension\Admin
 */
class RelatedCollectionExtension extends AbstractTypeExtension
{
    /**
     * RelatedCollectionExtension
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('related_collection', 'collection', array(
                'label' => '関連商品',
                'type' => 'admin_related_product',
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'mapped' => false,
            ))
        ;
    }

    /**
     * buildView
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
    }

    /**
     * product admin form name
     * @return string
     */
    public function getExtendedType()
    {
        return 'admin_product';
    }

}
