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

namespace Plugin\RelatedProduct\Form\Type\Admin;

use \Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\Extension\Core\Type;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\Validator\Constraints as Assert;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RelatedProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Product', 'entity', array(
                'class' => 'Eccube\Entity\Product',
                'required' => false,
                'mapped' => false,
            ))
            ->add('ChildProduct', 'entity', array(
                'label' => '関連商品',
                'class' => 'Eccube\Entity\Product',
                'required' => false,
            ))
            ->add('content', 'textarea', array(
                'label' => '説明文',
                'required' => false,
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Plugin\RelatedProduct\Entity\RelatedProduct',
        ));

    }

    public function getName()
    {
        return 'admin_related_product';
    }

}
