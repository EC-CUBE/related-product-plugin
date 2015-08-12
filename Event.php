<?php

namespace Plugin\RelatedProduct;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

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

        $twig = $app->renderView(
            'RelatedProduct/Resource/template/Front/related_product.twig',
            array(
                'RelatedProducts' => $RelatedProducts,
            )
        );

        $response = $event->getResponse();

        $html = $response->getContent();
        $crawler = new Crawler($html);

        $oldElement = $crawler
            ->filter('#main');

        $oldHtml = $oldElement->html();
        $newHtml = $oldHtml . $twig;

        $html = $crawler->html();
        $html = str_replace($oldHtml, $newHtml, $html);

        $response->setContent($html);
        $event->setResponse($response);
    }

    public function registerRelatedProduct()
    {
        $app = $this->app;
        $id = $app['request']->attributes->get('id');

        $form = $app['form.factory']
            ->createBuilder('admin_product')
            ->getForm();
        $form->handleRequest($app['request']);
        if ('POST' === $app['request']->getMethod()) {
            if ($form->isValid()) {
                $Product = $app['eccube.repository.product']->find($id);
                $app['eccube.plugin.repository.related_product']
                    ->removeChildProduct($Product);
                $app['orm.em']->flush();
                $RelatedProducts = $form->get('related_collection')->getData();
                foreach ($RelatedProducts as $RelatedProduct) {
                    /* @var $RelatedProduct \Plugin\RelatedProduct\Entity\RelatedProduct */
                    if ($RelatedProduct->getChildProduct() instanceof \Eccube\Entity\Product) {
                        $RelatedProduct
                            ->setProduct($Product);
                        $app['orm.em']->persist($RelatedProduct);
                    }
                }
                $app['orm.em']->flush();
            }
        }
    }

    public function addContentOnProductEdit(FilterResponseEvent $event)
    {
        $app = $this->app;
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
                'searchForm' => $searchForm->createView()
            )
        );
        $oldElement = $crawler
            ->filter('.accordion')
            ->last();
        if ($oldElement->count() > 0) {
            $oldHtml = $oldElement->html();
            $newHtml = $oldHtml . $twig;

            $html = $crawler->html() . $modal;
            $html = str_replace($oldHtml, $newHtml, $html);

            $response->setContent($html);
            $event->setResponse($response);
        }
    }

}