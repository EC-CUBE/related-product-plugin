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

namespace Plugin\RelatedProduct;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Event
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    // フロント：商品詳細画面に関連商品を表示
    public function showRelatedProduct(FilterResponseEvent $event)
    {
        $app = $this->app;

        $id = $app['request']->attributes->get('id');
        $Product = $app['eccube.repository.product']->find($id);
        $RelatedProducts = $app['eccube.plugin.repository.related_product']->findBy(array('Product' => $Product));

        if (count($RelatedProducts) > 0) {
            $twig = $app->renderView(
                'RelatedProduct/Resource/template/Front/related_product.twig',
                array(
                    'RelatedProducts' => $RelatedProducts,
                )
            );

            $response = $event->getResponse();

            if ($response instanceof RedirectResponse) {
                return;
            }

            $html = $response->getContent();
            $crawler = new Crawler($html);

            $oldHtml = $crawler->filter('#main')->html();
            $oldHtml = html_entity_decode($oldHtml, ENT_NOQUOTES, 'UTF-8');
            $newHtml = $oldHtml.$twig;

            $html = $this->getHtml($crawler);
            $html = str_replace($oldHtml, $newHtml, $html);

            $response->setContent($html);
            $event->setResponse($response);
        }
    }

    public function registerRelatedProduct(FilterResponseEvent $event)
    {
        $app = $this->app;
        if (!$app->isGranted('ROLE_ADMIN')) {
            return;
        }

        if ('POST' === $app['request']->getMethod()) {
            // ProductControllerの登録成功時のみ処理を通す
            // RedirectResponseかどうかで判定する.
            $response = $event->getResponse();
            if (!$response instanceof RedirectResponse) {
                return;
            }
            /* @var $Product \Eccube\Entity\Product */
            $Product = $this->getTargetProduct($event);
            $builder = $app['form.factory']->createBuilder('admin_product');

            if ($Product->hasProductClass()) {
                $builder->remove('class');
            }

            $form = $builder->getForm();
            $form->handleRequest($app['request']);

            if ($form->isValid()) {
                $app['eccube.plugin.repository.related_product']->removeChildProduct($Product);
                $app['orm.em']->flush();

                $RelatedProducts = $form->get('related_collection')->getData();
                foreach ($RelatedProducts as $RelatedProduct) {
                    /* @var $RelatedProduct \Plugin\RelatedProduct\Entity\RelatedProduct */
                    if ($RelatedProduct->getChildProduct() instanceof \Eccube\Entity\Product) {
                        $RelatedProduct->setProduct($Product);
                        $app['orm.em']->persist($RelatedProduct);
                    }
                }
                $app['orm.em']->flush();
            }
        }
    }

    private function getTargetProduct($event)
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

    public function addContentOnProductEdit(FilterResponseEvent $event)
    {
        $app = $this->app;
        if (!$app->isGranted('ROLE_ADMIN')) {
            return;
        }
        $request = $event->getRequest();
        $response = $event->getResponse();
        $id = $request->attributes->get('id');

        $html = $response->getContent();
        $crawler = new Crawler($html);

        $form = $app['form.factory']
            ->createBuilder('admin_product')
            ->getForm();

        if ($id) {
            $Product = $app['eccube.repository.product']->find($id);
        } else {
            $Product = new \Eccube\Entity\Product();
        }

        $RelatedProducts = $app['eccube.plugin.repository.related_product']->findBy(
            array(
                'Product' => $Product,
            ));

        $loop = 5 - count($RelatedProducts);
        for ($i = 0; $i < $loop; $i++) {
            $RelatedProduct = new \Plugin\RelatedProduct\Entity\RelatedProduct();
            $RelatedProduct
                ->setProductId($id)
                ->setProduct($Product);
            $RelatedProducts[] = $RelatedProduct;
        }
        $form->get('related_collection')->setData($RelatedProducts);

        $form->handleRequest($request);

        // 商品検索フォーム
        $searchForm = $app['form.factory']
            ->createBuilder('admin_search_product')
            ->getForm();

        $twig = $app->renderView(
            'RelatedProduct/Resource/template/Admin/related_product.twig',
            array(
                'form' => $form->createView(),
            )
        );
        $modal = $app->renderView(
            'RelatedProduct/Resource/template/Admin/modal.twig',
            array(
                'searchForm' => $searchForm->createView(),
                'Product' => $Product,
            )
        );
        $oldElement = $crawler
            ->filter('.accordion')
            ->last();
        if ($oldElement->count() > 0) {
            $oldHtml = $oldElement->html();
            $newHtml = $oldHtml.$twig;

            $html = $this->getHtml($crawler);
            $html = $html.$modal;
            $html = str_replace($oldHtml, $newHtml, $html);

            $response->setContent($html);
            $event->setResponse($response);
        }
    }

    /**
     * 解析用HTMLを取得
     *
     * @param Crawler $crawler
     * @return string
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

}
