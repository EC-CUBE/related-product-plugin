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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class RelatedProductType extends AbstractType
{
    /**
     * @var \Eccube\Application
     */
    private $app;

    /**
     * RelatedProductType constructor.
     * @param $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * RelatedProduct form builder
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $app = $this->app;
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
                'trim' => true,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $app['config']['text_area_len'],
                    )),
                ),
                'attr' => array(
                    'maxlength' => $app['config']['text_area_len'],
                    'placeholder' => $app->trans('plugin.related_product.type.comment.placeholder')),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Plugin\RelatedProduct\Entity\RelatedProduct',
        ));

    }

    /**
     * form name
     * @return string
     */
    public function getName()
    {
        return 'admin_related_product';
    }

}
