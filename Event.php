<?php
/*
 * This file is part of the Related Product plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Plugin\RelatedProduct;

use Doctrine\Common\Collections\Collection;
use Eccube\Common\Constant;
use Eccube\Application;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Eccube\Entity\Product;
use Plugin\RelatedProduct\Entity\RelatedProduct;
use Eccube\Entity\Master\Disp;
use Eccube\Event\TemplateEvent;
use Eccube\Event\EventArgs;

/**
 * Class Event for  new hook point on version >= 3.0.9.
 */
class Event
{
    /**
     * @var Application
     */
    private $app;

    /**
     * v3.0.0 - 3.0.8 向けのイベントを処理するインスタンス.
     *
     * @var EventLegacy
     */
    private $legacyEvent;

    /**
     * position for insert in twig file.
     *
     * @var string
     */
    const RELATED_PRODUCT_TAG = '<!--# RelatedProductPlugin-Tag #-->';

    /**
     * maximum product related.
     *
     * @var int
     */
    const MAXIMUM_PRODUCT_RELATED = 5;

    /**
     * Event constructor.
     *
     * @param Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->legacyEvent = new EventLegacy($app);
    }

    /**
     * フロント：商品詳細画面に関連商品を表示.
     *
     * @param TemplateEvent $event
     */
    public function onRenderProductDetail(TemplateEvent $event)
    {
        $app = $this->app;
        $parameters = $event->getParameters();
        // ProductIDがない場合、レンダリングしない
        if (is_null($parameters['Product'])) {
            return;
        }

        // 登録がない、レンダリングをしない
        $Product = $parameters['Product'];
        $Disp = $app['eccube.repository.master.disp']->find(Disp::DISPLAY_SHOW);
        $RelatedProducts = $app['eccube.plugin.repository.related_product']->showRelatedProduct($Product, $Disp);
        if (count($RelatedProducts) == 0) {
            return;
        }

        // twigコードを挿入
        $snipet = $app['twig']->getLoader()->getSource('RelatedProduct/Resource/template/front/related_product.twig');
        $source = $event->getSource();
        //find related product mark
        if (strpos($source, self::RELATED_PRODUCT_TAG)) {
            $search = self::RELATED_PRODUCT_TAG;
        } else {
            //regular expression for get free area div
            $pattern = '/({% if Product.freearea %})(.*?(\n))+.*?({% endif %})/';
            preg_match($pattern, $source, $matches);
            $search = $matches[0];
        }
        $replace = $snipet.$search;
        $source = str_replace($search, $replace, $source);
        $event->setSource($source);

        //set parameter for twig files
        $parameters['RelatedProducts'] = $RelatedProducts;
        $event->setParameters($parameters);
    }

    /**
     * new hookpoint for init product edit.
     *
     * @param EventArgs $event
     */
    public function onRenderAdminProductInit(EventArgs $event)
    {
        $Product = $event->getArgument('Product');
        $RelatedProducts = $this->createRelatedProductData($Product);

        // フォームの追加
        /** @var FormBuilder $builder */
        $builder = $event->getArgument('builder');
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
        $builder->get('related_collection')->setData($RelatedProducts);
    }

    /**
     * new hookpoint for render RelatedProduct form.
     *
     * @param TemplateEvent $event
     */
    public function onRenderAdminProduct(TemplateEvent $event)
    {
        $app = $this->app;
        $parameters = $event->getParameters();
        $Product = $parameters['Product'];
        $form = $parameters['form'];
        $searchForm = $parameters['searchForm'];
        $RelatedProducts = $this->createRelatedProductData($Product);

        // twigコードを挿入
        $snipet = $app['twig']->getLoader()->getSource('RelatedProduct/Resource/template/admin/related_product.twig');
        $modal = $app['twig']->getLoader()->getSource('RelatedProduct/Resource/template/admin/modal.twig');

        //add related product to product edit
        $search = '<div id="detail_box__footer" class="row hidden-xs hidden-sm">';
        $source = $event->getSource();
        $replaceMain = $search.$snipet;
        $source = str_replace($search, $replaceMain, $source);
        $event->setSource($source.$modal);

        //set parameter for twig files
        $parameters['form'] = $form;
        $parameters['RelatedProducts'] = $RelatedProducts;
        $parameters['searchForm'] = $searchForm;
        $parameters['Product'] = $Product;
        $event->setParameters($parameters);
    }

    /**
     * new hookpoint for save RelatedProduct.
     *
     * @param EventArgs $event
     */
    public function onRenderAdminProductComplete(EventArgs  $event)
    {
        $app = $this->app;
        $Product = $event->getArgument('Product');
        $form = $event->getArgument('form');
        $app['eccube.plugin.repository.related_product']->removeChildProduct($Product);
        $RelatedProducts = $form->get('related_collection')->getData();
        foreach ($RelatedProducts as $RelatedProduct) {
            /* @var $RelatedProduct \Plugin\RelatedProduct\Entity\RelatedProduct */
            if ($RelatedProduct->getChildProduct() instanceof Product) {
                $RelatedProduct->setProduct($Product);
                $app['orm.em']->persist($RelatedProduct);
                $app['orm.em']->flush($RelatedProduct);
            }
        }
    }

    /**
     * for v3.0.0 - 3.0.8.
     *
     * @deprecated for since v3.0.0, to be removed in 3.1
     *
     * @param FilterResponseEvent $event
     */
    public function onRenderProductDetailBefore(FilterResponseEvent $event)
    {
        if ($this->supportNewHookPoint()) {
            return;
        }
        $this->legacyEvent->onRenderProductDetailBefore($event);
    }

    /**
     * for v3.0.0 - 3.0.8.
     *
     * @deprecated for since v3.0.0, to be removed in 3.1
     *
     * @param FilterResponseEvent $event
     */
    public function onRenderAdminProductEditBefore(FilterResponseEvent $event)
    {
        if ($this->supportNewHookPoint()) {
            return;
        }
        $this->legacyEvent->onRenderAdminProductEditBefore($event);
    }

    /**
     * v3.0.9以降のフックポイントに対応しているのか.
     *
     * @return bool
     */
    private function supportNewHookPoint()
    {
        return version_compare('3.0.9', Constant::VERSION, '<=');
    }

    /**
     * @param Product $Product
     *
     * @return Collection RelatedProducts
     */
    private function createRelatedProductData($Product)
    {
        $app = $this->app;
        $RelatedProducts = null;
        if ($Product) {
            $RelatedProducts = $app['eccube.plugin.repository.related_product']->findBy(
                array(
                    'Product' => $Product,
                ));
        } else {
            $Product = new Product();
        }
        $loop = self::MAXIMUM_PRODUCT_RELATED - count($RelatedProducts);
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
