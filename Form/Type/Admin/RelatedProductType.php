<?php
/*
 * This file is part of the Related Product plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RelatedProduct\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class RelatedProductType.
 */
class RelatedProductType extends AbstractType
{
    /**
     * @var \Eccube\Application
     */
    private $app;

    /**
     * RelatedProductType constructor.
     *
     * @param \Eccube\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * RelatedProduct form builder.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $app = $this->app;
        $builder
            ->add(
                $builder
                    ->create('Product', 'hidden', array(
                        'required' => false,
                        'mapped' => false,
                    ))
                    ->addModelTransformer(new \Eccube\Form\DataTransformer\EntityToIdTransformer($app['orm.em'], 'Eccube\Entity\Product'))
            )
            ->add(
                $builder
                    ->create('ChildProduct', 'hidden', array(
                        'label' => '関連商品',
                        'required' => false,
                    ))
                    ->addModelTransformer(new \Eccube\Form\DataTransformer\EntityToIdTransformer($app['orm.em'], 'Eccube\Entity\Product'))
            )
            ->add('content', 'textarea', array(
                'label' => '説明文',
                'required' => false,
                'trim' => true,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $app['config']['related_product_text_area_len'],
                    )),
                ),
                'attr' => array(
                    'maxlength' => $app['config']['related_product_text_area_len'],
                    'placeholder' => $app->trans('plugin.related_product.type.comment.placeholder'),
                ),
            ));
    }

    /**
     * configureOptions.
     * {@inheritdoc}
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Plugin\RelatedProduct\Entity\RelatedProduct',
        ));
    }

    /**
     * form name.
     *
     * @return string
     */
    public function getName()
    {
        return 'admin_related_product';
    }
}
