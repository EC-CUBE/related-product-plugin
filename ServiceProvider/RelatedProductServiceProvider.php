<?php
/*
 * This file is part of the Related Product plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RelatedProduct\ServiceProvider;

use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;
use Plugin\RelatedProduct\Form\Type\Admin\RelatedProductType;
use Plugin\RelatedProduct\Form\Extension\Admin\RelatedCollectionExtension;
use Silex\Application;
use Eccube\Common\Constant;

/**
 * Class RelatedProductServiceProvider.
 */
class RelatedProductServiceProvider implements ServiceProviderInterface
{
    /**
     * register.
     *
     * @param Application $app
     */
    public function register(BaseApplication $app)
    {
        $app->post('/related_product/search_product', '\Plugin\RelatedProduct\Controller\Admin\RelatedProductController::searchProduct')
            ->bind('admin_related_product_search');

        $app->match('/'.$app['config']['admin_route'].'/related_product/search/product/page/{page_no}', '\Plugin\RelatedProduct\Controller\Admin\RelatedProductController::searchProduct')
            ->assert('page_no', '\d+')
            ->bind('admin_related_product_search_product_page');

        // Formの定義
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new RelatedProductType($app);

            return $types;
        }));

        // @deprecated for since v3.0.0, to be removed in 3.1.
        if (version_compare(Constant::VERSION, '3.0.9', '<')) {
            // Form/Extension
            $app['form.type.extensions'] = $app->share(
                $app->extend(
                    'form.type.extensions',
                    function ($extensions) {
                        $extensions[] = new RelatedCollectionExtension();

                        return $extensions;
                    }
                )
            );
        }

        // Repository
        $app['eccube.plugin.repository.related_product'] = function () use ($app) {
            return $app['orm.em']->getRepository('\Plugin\RelatedProduct\Entity\RelatedProduct');
        };

        // メッセージ登録
        $app['translator'] = $app->share($app->extend('translator', function ($translator, Application $app) {
            $file = __DIR__.'/../Resource/locale/message.'.$app['locale'].'.yml';
            if (file_exists($file)) {
                $translator->addResource('yaml', $file, $app['locale']);
            }

            return $translator;
        }));

        // Add config file.
        $app['config'] = $app->share($app->extend('config', function ($config) {
            // Update constants
            $constantFile = __DIR__.'/../Resource/config/constant.yml';
            if (file_exists($constantFile)) {
                $constant = Yaml::parse(file_get_contents($constantFile));
                if (!empty($constant)) {
                    // Replace constants
                    $config = array_replace_recursive($config, $constant);
                }
            }

            return $config;
        }));
    }

    /**
     * boot.
     *
     * @param Application $app
     */
    public function boot(BaseApplication $app)
    {
    }
}
