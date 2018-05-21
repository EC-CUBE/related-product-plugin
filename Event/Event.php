<?php
/*
 * This file is part of the Related Product plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Plugin\RelatedProduct\Event;

use Symfony\Component\Form\FormBuilder;
use Eccube\Entity\Product;
use Plugin\RelatedProduct\Entity\RelatedProduct;
use Plugin\RelatedProduct\Repository\RelatedProductRepository;
use Eccube\Event\TemplateEvent;
use Eccube\Event\EventArgs;
use Eccube\Common\EccubeConfig;
use Eccube\Repository\Master\ProductStatusRepository;
use Eccube\Entity\Master\ProductStatus;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Plugin\RelatedProduct\Form\Type\Admin\RelatedProductType;
use Doctrine\ORM\EntityManagerInterface;

class Event
{
    /**
     * position for insert in twig file.
     *
     * @var string
     */
    const RELATED_PRODUCT_TAG = '<!--# related-product-plugin-tag #-->';

    /**
     * @var RelatedProductRepository
     */
    protected $relatedProductRepository;

    /**
     * @var ProductStatusRepository
     */
    protected $productStatusRepository;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var \Twig_Environment
     */
    protected $twigEnvironment;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Event constructor.
     *
     * @param ProductStatusRepository $productStatusRepository
     * @param RelatedProductRepository $relatedProductRepository
     * @param EccubeConfig $eccubeConfig
     * @param \Twig_Environment $twigEnvironment
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        ProductStatusRepository $productStatusRepository,
        RelatedProductRepository $relatedProductRepository,
        EccubeConfig $eccubeConfig,
        \Twig_Environment $twigEnvironment,
        EntityManagerInterface $entityManager
    ) {
        $this->productStatusRepository = $productStatusRepository;
        $this->relatedProductRepository = $relatedProductRepository;
        $this->eccubeConfig = $eccubeConfig;
        $this->twigEnvironment = $twigEnvironment;
        $this->entityManager = $entityManager;
    }

    /**
     * フロント：商品詳細画面に関連商品を表示.
     *
     * @param TemplateEvent $event
     */
    public function onRenderProductDetail(TemplateEvent $event)
    {
        log_info('RelatedProduct trigger onRenderProductDetail start');
        $parameters = $event->getParameters();
        // ProductIDがない場合、レンダリングしない
        if (is_null($parameters['Product'])) {
            return;
        }

        // 登録がない、レンダリングをしない
        $Product = $parameters['Product'];
        /** @var ProductStatus $ProductStatus */
        $ProductStatus = $this->productStatusRepository->find(ProductStatus::DISPLAY_SHOW);
        $RelatedProducts = $this->relatedProductRepository->showRelatedProduct($Product, $ProductStatus);
        if (count($RelatedProducts) == 0) {
            return;
        }

        // twigコードを挿入
        try {
            $snipet = $this->twigEnvironment->getLoader()
                ->getSourceContext('RelatedProduct/Resource/template/front/related_product.twig')
                ->getCode();
        } catch (\Exception $e) {
            log_info('Event: related product render error.', [$e]);
            $snipet = '';
        }
        $source = $event->getSource();
        //find related product mark
        if (strpos($source, self::RELATED_PRODUCT_TAG)) {
            log_info('Render related product with ', ['RELATED_PRODUCT_TAG' => self::RELATED_PRODUCT_TAG]);
            $search = self::RELATED_PRODUCT_TAG;
            $replace = $search.$snipet;
        } else {
            // As request, the related product area will append bellow free area section.
            $freeAreaStart = '{% if Product.freearea %}';
            $pos = strpos($source, $freeAreaStart);
            $search = substr($source, $pos);
            // End of free area
            $freeAreaEnd = '{% endif %}';
            $from = '/'.preg_quote($freeAreaEnd, '/').'/';
            $replace = preg_replace($from, $freeAreaEnd.$snipet, $search, 1);
        }
        $source = str_replace($search, $replace, $source);
        $event->setSource($source);

        //set parameter for twig files
        $parameters['RelatedProducts'] = $RelatedProducts;
        $event->setParameters($parameters);
        log_info('RelatedProduct trigger onRenderProductDetail finish');
    }

    /**
     * new hookpoint for init product edit.
     *
     * @param EventArgs $event
     */
    public function onAdminProductEditInitialize(EventArgs $event)
    {
        log_info('RelatedProduct trigger onRenderAdminProductInit start');
        $Product = $event->getArgument('Product');
        $RelatedProducts = $this->createRelatedProductData($Product);
        // フォームの追加
        /** @var FormBuilder $builder */
        $builder = $event->getArgument('builder');
        $builder
            ->add('related_collection', CollectionType::class, [
                'label' => 'plugin.related_product.block.title',
                'entry_type' => RelatedProductType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'mapped' => false,
            ])
        ;
        $builder->get('related_collection')->setData($RelatedProducts);
        log_info('RelatedProduct trigger onRenderAdminProductInit finish');
    }

    /**
     * new hookpoint for render RelatedProduct form.
     *
     * @param TemplateEvent $event
     */
    public function onRenderAdminProduct(TemplateEvent $event)
    {
        log_info('RelatedProduct trigger onRenderAdminProduct start');
        $parameters = $event->getParameters();
        $Product = $parameters['Product'];
        $RelatedProducts = $this->createRelatedProductData($Product);

        // twigコードを挿入
        try {
            $snipet = $this->twigEnvironment->getLoader()->getSourceContext('RelatedProduct/Resource/template/admin/related_product.twig');
            $snipet = $snipet->getCode();
            $modal = $this->twigEnvironment->getLoader()->getSourceContext('RelatedProduct/Resource/template/admin/modal.twig');
            $modal = $modal->getCode();
        } catch (\Exception $e) {
            $snipet = '';
            $modal = '';
        }

        //add related product to product edit
        // TODO: should find a way better to insert/edit template
        // TODO: @admin/Product/product.twig should have a anchor (id, or comment tag) to insert/edit template
        $search = '<!-- layout.admin.product.primaryCol.footer -->';
        $source = $event->getSource();
        $replace = $search.$modal.$snipet;
        $source = str_replace($search, $replace, $source);
        $event->setSource($source);

        //set parameter for twig files
        $existsRelativeProducts = array_filter($RelatedProducts, function ($v) {
            return !is_null($v->getChildProduct());
        });
        $parameters['toggleActive'] = (count($existsRelativeProducts) > 0);
        $parameters['RelatedProducts'] = $RelatedProducts;
        $event->setParameters($parameters);
        log_info('RelatedProduct trigger onRenderAdminProduct finish');
    }

    /**
     * new hookpoint for save RelatedProduct.
     *
     * @param EventArgs $event
     */
    public function onAdminProductEditComplete(EventArgs  $event)
    {
        log_info('RelatedProduct trigger onRenderAdminProductComplete start');
        try {
            $Product = $event->getArgument('Product');
            $form = $event->getArgument('form');
            $this->relatedProductRepository->removeChildProduct($Product);
            log_info('remove all now related product data of ', ['Product id' => $Product->getId()]);
            $RelatedProducts = $form->get('related_collection')->getData();
            foreach ($RelatedProducts as $RelatedProduct) {
                /* @var $RelatedProduct \Plugin\RelatedProduct\Entity\RelatedProduct */
                if ($RelatedProduct->getChildProduct() instanceof Product) {
                    $RelatedProduct->setProduct($Product);
                    $this->entityManager->persist($RelatedProduct);
                    $this->entityManager->flush($RelatedProduct);
                    log_info('save new related product data to DB ', ['Related Product id' => $RelatedProduct->getId()]);
                }
            }
        } catch (\Exception $e) {
            log_error('RelatedProduct trigger onRenderAdminProductComplete error', [$e]);
        }
        log_info('RelatedProduct trigger onRenderAdminProductComplete finish');
    }

    /**
     * @param Product $Product
     *
     * @return array RelatedProducts
     */
    private function createRelatedProductData($Product)
    {
        $RelatedProducts = null;
        $id = $Product->getId();
        if ($id) {
            $RelatedProducts = $this->relatedProductRepository->getRelatedProduct($Product);
        } else {
            $Product = new Product();
        }
        
        $maxCount = $this->eccubeConfig['related_product_max_item_count'];
        $loop = $maxCount - count($RelatedProducts);
        for ($i = 0; $i < $loop; ++$i) {
            $RelatedProduct = new RelatedProduct();
            $RelatedProduct
                ->setProductId($Product->getId())
                ->setProduct($Product);
            $RelatedProducts[] = $RelatedProduct;
        }

        return $RelatedProducts;
    }
}
