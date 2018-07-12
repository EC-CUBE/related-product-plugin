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

namespace Plugin\RelatedProduct\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Form\DataTransformer\EntityToIdTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Entity\Product;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Translation\TranslatorInterface;
use Plugin\RelatedProduct\Entity\RelatedProduct;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Class RelatedProductType.
 */
class RelatedProductType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * RelatedProductType constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param EccubeConfig $eccubeConfig
     * @param TranslatorInterface $translator
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        EccubeConfig $eccubeConfig
    ) {
        $this->entityManager = $entityManager;
        $this->eccubeConfig = $eccubeConfig;
        $this->translator = $translator;
    }

    /**
     * RelatedProduct form builder.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            $builder->create('Product', HiddenType::class, [
                'required' => false,
                'mapped' => false,
            ])->addModelTransformer(new EntityToIdTransformer($this->entityManager, Product::class))
        )->add(
            $builder->create('ChildProduct', HiddenType::class, [
                'label' => '関連商品',
                'required' => false,
            ])->addModelTransformer(new EntityToIdTransformer($this->entityManager, Product::class))
        )->add('content', TextareaType::class, [
            'label' => '説明文',
            'required' => false,
            'trim' => true,
            'constraints' => [
                new Assert\Length([
                    'max' => $this->eccubeConfig['related_product.text_area_len'],
                ]),
            ],
            'attr' => [
                'maxlength' => $this->eccubeConfig['related_product.text_area_len'],
                'placeholder' => $this->translator->trans('related_product.type.comment.placeholder'),
            ],
        ]);
    }

    /**
     * configureOptions.
     * {@inheritdoc}
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RelatedProduct::class,
        ]);
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
