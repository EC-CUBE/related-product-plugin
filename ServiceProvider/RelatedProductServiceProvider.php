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

namespace Plugin\RelatedProduct\ServiceProvider;

use Eccube\Application;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

class RelatedProductServiceProvider implements ServiceProviderInterface
{
    public function register(BaseApplication $app)
    {
        $app->post('/related_product/search_product', '\Plugin\RelatedProduct\Controller\Admin\RelatedProductController::searchProduct')
            ->bind('admin_related_product_search');
        // Formの定義
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new \Plugin\RelatedProduct\Form\Type\Admin\RelatedProductType();

            return $types;
        }));
        $app['form.type.extensions'] = $app->share($app->extend('form.type.extensions', function ($extensions) use ($app) {
            $extensions[] = new \Plugin\RelatedProduct\Form\Extension\Admin\RelatedCollectionExtension();

            return $extensions;
        }));

        // Repositoy
        $app['eccube.plugin.repository.related_product'] = function () use ($app) {
            return $app['orm.em']->getRepository('\Plugin\RelatedProduct\Entity\RelatedProduct');
        };

        // Service
        $app['eccube.plugin.service.related_product'] = $app->share(function () use ($app) {
            return new \Plugin\RelatedProduct\Service\RelatedProductService($app);
        });
    }

    public function boot(BaseApplication $app)
    {
    }
}