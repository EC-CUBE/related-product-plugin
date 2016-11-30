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

use Eccube\Application;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Eccube\Entity\Product;
use Plugin\RelatedProduct\Entity\RelatedProduct;
use Eccube\Entity\Master\Disp;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Eccube\Common\Constant;

/**
 * Class EventLegacy.
 */
class EventLegacy
{
    /**
     * @var Application
     */
    private $app;

    /**
     * position for insert in twig file.
     *
     * @var string
     */
    const RELATED_PRODUCT_TAG = '<!--# related-product-plugin-tag #-->';

    /**
     * Event constructor.
     *
     * @param Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * フロント：商品詳細画面に関連商品を表示.
     *
     * @param FilterResponseEvent $event
     */
    public function onRenderProductDetailBefore(FilterResponseEvent $event)
    {
        log_info('RelatedProduct trigger onRenderProductDetailBefore start');
        $app = $this->app;
        $id = $app['request']->attributes->get('id');
        $Disp = $app['eccube.repository.master.disp']->find(Disp::DISPLAY_SHOW);
        $Product = $app['eccube.repository.product']->find($id);
        $RelatedProducts = $app['eccube.plugin.repository.related_product']->showRelatedProduct($Product, $Disp);

        if (count($RelatedProducts) > 0) {
            $twig = $app->renderView(
                'RelatedProduct/Resource/template/front/related_product.twig',
                array(
                    'RelatedProducts' => $RelatedProducts,
                )
            );

            $response = $event->getResponse();

            if ($response instanceof RedirectResponse) {
                return;
            }

            $html = $response->getContent();
            $search = null;
            //find related product mark
            if (strpos($html, self::RELATED_PRODUCT_TAG)) {
                log_info('Render related product with ', array('RELATED_PRODUCT_TAG' => self::RELATED_PRODUCT_TAG));
                $search = self::RELATED_PRODUCT_TAG;
                $replace = $search.$twig;
                $html = str_replace($search, $replace, $html);
            } else {
                // For old and new version
                $crawler = new Crawler($html);
                $oldHtml = $crawler->filter('#main')->html();
                $oldHtml = html_entity_decode($oldHtml, ENT_NOQUOTES, 'UTF-8');
                $newHtml = $oldHtml.$twig;
                $html = $this->getHtml($crawler);
                $html = str_replace($oldHtml, $newHtml, $html);
            }

            $response->setContent($html);
            $event->setResponse($response);
        }
        log_info('RelatedProduct trigger onRenderProductDetailBefore finish');
    }

    /**
     * add RelatedProduct to product edit.
     *
     * @param FilterResponseEvent $event
     */
    public function onRenderAdminProductEditBefore(FilterResponseEvent $event)
    {
        log_info('RelatedProduct trigger onRenderAdminProductEditBefore start');
        $app = $this->app;
        if (!$app->isGranted('ROLE_ADMIN')) {
            return;
        }
        $request = $event->getRequest();
        $response = $event->getResponse();

        $builder = $app['form.factory']->createBuilder('admin_product');
        $form = $builder->getForm();
        $form->handleRequest($request);
        $html = $this->addRelatedProductToAdminProduct($request, $response);
        $response->setContent($html);
        $event->setResponse($response);

        if ($form->isSubmitted()) {
            // ProductControllerの登録成功時のみ処理を通す
            // RedirectResponseかどうかで判定する.
            if (!$response instanceof RedirectResponse) {
                return;
            }
            /* @var $Product \Eccube\Entity\Product */
            $Product = $this->getTargetProduct($event);

            if ($Product->hasProductClass()) {
                $builder->remove('class');
                //get new form from builder.
                $form = $builder->getForm();
                $form->handleRequest($request);
            }
            if ($form['related_collection']->isValid()) {
                $app['eccube.plugin.repository.related_product']->removeChildProduct($Product);
                log_info('remove all now related product data of ', array('Product id' => $Product->getId()));
                $RelatedProducts = $form->get('related_collection')->getData();
                foreach ($RelatedProducts as $RelatedProduct) {
                    /* @var $RelatedProduct \Plugin\RelatedProduct\Entity\RelatedProduct */
                    if ($RelatedProduct->getChildProduct() instanceof Product) {
                        $RelatedProduct->setProduct($Product);
                        $app['orm.em']->persist($RelatedProduct);
                        $app['orm.em']->flush($RelatedProduct);
                        log_info('save new related product data to DB ', array('Related Product id' => $RelatedProduct->getId()));
                    }
                }
            }
        }
        log_info('RelatedProduct trigger onRenderAdminProductEditBefore finish');
    }

    /**
     * getTargetProduct.
     *
     * @param FilterResponseEvent $event
     *
     * @return Product $Product
     */
    private function getTargetProduct(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        if ($request->attributes->get('id')) {
            $id = $request->attributes->get('id');
        } else {
            $location = explode('/', $response->headers->get('location'));
            $url = explode('/', $this->app->url('admin_product_product_edit', array('id' => '0')));
            $diffs = array_values(array_diff($location, $url));
            $id = $diffs[0];
        }

        $Product = $this->app['eccube.repository.product']->find($id);

        return $Product;
    }

    /**
     * 解析用HTMLを取得.
     *
     * @param Crawler $crawler
     *
     * @return string html
     */
    private function getHtml(Crawler $crawler)
    {
        $html = '';
        foreach ($crawler as $domElement) {
            $domElement->ownerDocument->formatOutput = true;
            $html .= $domElement->ownerDocument->saveHTML();
        }

        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
    }

    /**
     * add related product form to admin product.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return string $html
     */
    private function addRelatedProductToAdminProduct(Request $request, Response $response)
    {
        $app = $this->app;
        $id = $request->attributes->get('id');
        $RelatedProducts = null;

        if ($id) {
            $Product = $app['eccube.repository.product']->find($id);
            $RelatedProducts = $app['eccube.plugin.repository.related_product']->getRelatedProduct($Product, Constant::DISABLED);
        } else {
            $Product = new Product();
        }

        $maxCount = $app['config']['related_product_max_item_count'];
        $loop = $maxCount - count($RelatedProducts);
        for ($i = 0; $i < $loop; ++$i) {
            $RelatedProduct = new RelatedProduct();
            $RelatedProduct
                ->setProductId($id)
                ->setProduct($Product);
            $RelatedProducts[] = $RelatedProduct;
        }
        $builder = $app['form.factory']->createBuilder('admin_product');
        $form = $builder->getForm();
        $form->get('related_collection')->setData($RelatedProducts);
        // 商品検索フォーム
        $searchForm = $app['form.factory']
            ->createBuilder('admin_search_product')
            ->getForm();

        $twig = $app->renderView(
            'RelatedProduct/Resource/template/admin/related_product.twig',
            array(
                'form' => $form->createView(),
                'RelatedProducts' => $RelatedProducts,
                'searchForm' => $searchForm->createView(),
                'Product' => $Product,
            )
        );
        $modal = $app->renderView(
            'RelatedProduct/Resource/template/admin/modal.twig',
            array(
                'searchForm' => $searchForm->createView(),
                'Product' => $Product,
            )
        );
        $html = $response->getContent();
        $html = $html.$modal;
        // For old and new version
        $search = '/(<div class="row hidden-xs hidden-sm")|(<div id="detail_box__footer")/';
        $newHtml = $twig.'<div id="detail_box__footer" class="row hidden-xs hidden-sm"';
        $html = preg_replace($search, $newHtml, $html);

        return $html;
    }
}
