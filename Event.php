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

use Eccube\Application;
use Plugin\RelatedProduct\Utils\Version;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
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
     * @param TemplateEvent $event
     */
    public function onRenderProductDetail(TemplateEvent $event)
    {
        $this->app['eccube.plugin.relatedproduct.event']->onRenderProductDetail($event);
    }

    /**
     * new hookpoint for init product edit.
     *
     * @param EventArgs $event
     */
    public function onRenderAdminProductInit(EventArgs $event)
    {
        $this->app['eccube.plugin.relatedproduct.event']->onRenderAdminProductInit($event);
    }

    /**
     * new hookpoint for render RelatedProduct form.
     *
     * @param TemplateEvent $event
     */
    public function onRenderAdminProduct(TemplateEvent $event)
    {
        $this->app['eccube.plugin.relatedproduct.event']->onRenderAdminProduct($event);
    }

    /**
     * new hookpoint for save RelatedProduct.
     *
     * @param EventArgs $event
     */
    public function onRenderAdminProductComplete(EventArgs  $event)
    {
        $this->app['eccube.plugin.relatedproduct.event']->onRenderAdminProductComplete($event);
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
        //current version >= 3.0.9
        if (Version::isSupportNewHookpoint()) {
            return;
        }
        $this->app['eccube.plugin.relatedproduct.event.legacy']->onRenderProductDetailBefore($event);
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
        //current version >= 3.0.9
        if (Version::isSupportNewHookpoint()) {
            return;
        }
        $this->app['eccube.plugin.relatedproduct.event.legacy']->onRenderAdminProductEditBefore($event);
    }
}
