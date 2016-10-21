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

use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;
use Plugin\RelatedProduct\Form\Type\Admin\RelatedProductType;
use Plugin\RelatedProduct\Form\Extension\Admin\RelatedCollectionExtension;
use Plugin\RelatedProduct\Service\RelatedProductService;
use Silex\Application;

class RelatedProductServiceProvider implements ServiceProviderInterface
{
    public function register(BaseApplication $app)
    {
        $app->post('/related_product/search_product', '\Plugin\RelatedProduct\Controller\Admin\RelatedProductController::searchProduct')
            ->bind('admin_related_product_search');

        $app->match('/'.$app["config"]["admin_route"].'/related_product/search/product/page/{page_no}', '\Plugin\RelatedProduct\Controller\Admin\RelatedProductController::searchProduct')
            ->assert('page_no', '\d+')
            ->bind('admin_related_product_search_product_page');

        // Formの定義
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new RelatedProductType($app);

            return $types;
        }));
        $app['form.type.extensions'] = $app->share($app->extend('form.type.extensions', function ($extensions) use ($app) {
            $extensions[] = new RelatedCollectionExtension();

            return $extensions;
        }));

        // Repositoy
        $app['eccube.plugin.repository.related_product'] = function () use ($app) {
            return $app['orm.em']->getRepository('\Plugin\RelatedProduct\Entity\RelatedProduct');
        };

        // Service
        $app['eccube.plugin.service.related_product'] = $app->share(function () use ($app) {
            return new RelatedProductService($app);
        });

        // メッセージ登録
        $app['translator'] = $app->share($app->extend('translator', function ($translator, Application $app) {
            $file = __DIR__.'/../Resource/locale/message.'.$app['locale'].'.yml';
            if (file_exists($file)) {
                $translator->addResource('yaml', $file, $app['locale']);
            }

            return $translator;
        }));

        // Add config file
        $app['config'] = $app->share($app->extend('config', function ($config) {
            // Update path
            $pathFile = __DIR__.'/../Resource/config/path.yml';
            if (file_exists($pathFile)) {
                $path = Yaml::parse(file_get_contents($pathFile));
                if (!empty($path)) {
                    // Replace path
                    $config = array_replace_recursive($config, $path);
                }
            }

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

        // ログファイル設定
        $app['monolog.RelatedProduct'] = $app->share(function ($app) {
            $loggerClass = $app['monolog.logger.class'];
            $loggerName = 'plugin.RelatedProduct';
            $logger = new $loggerClass($loggerName);

            $file = $app['config']['root_dir'].'/app/log/RelatedProduct.log';
            $rotateHandler = new RotatingFileHandler($file, $app['config']['log']['max_files'], Logger::INFO);
            $rotateHandler->setFilenameFormat(
                'RelatedProduct_{date}',
                'Y-m-d'
            );

            $logger->pushHandler(
                new FingersCrossedHandler(
                    $rotateHandler,
                    new ErrorLevelActivationStrategy(Logger::INFO)
                )
            );

            return $logger;
        });
    }

    public function boot(BaseApplication $app)
    {
    }
}