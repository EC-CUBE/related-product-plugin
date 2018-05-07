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
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Eccube\Event\TemplateEvent;
use Eccube\Event\EventArgs;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Plugin\RelatedProduct\Event\Event;

/**
 * Class Event for  new hook point on version >= 3.0.9.
 */
class EventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var Event
     */
    protected $event;

    /**
     * EventSubscriber constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public static function getSubscribedEvents()
    {
        return [
            'admin.product.edit.initialize' => [['onAdminProductEditInitialize', 10]],
            'Admin/@admin/Product/product.twig' => [['onRenderAdminProduct', 10]],
            'admin.product.edit.complete' => [['onAdminProductEditComplete', 10]],
            'Product/detail.twig' => [['onRenderProductDetail', 10]],
        ];
    }


    /**
     * フロント：商品詳細画面に関連商品を表示.
     *
     * @param TemplateEvent $event
     */
    public function onRenderProductDetail(TemplateEvent $event)
    {
        $this->event->onRenderProductDetail($event);
    }

    /**
     * new hookpoint for init product edit.
     *
     * @param EventArgs $event
     */
    public function onAdminProductEditInitialize(EventArgs $event)
    {
        $this->event->onAdminProductEditInitialize($event);
    }

    /**
     * new hookpoint for render RelatedProduct form.
     *
     * @param TemplateEvent $event
     */
    public function onRenderAdminProduct(TemplateEvent $event)
    {
        $this->event->onRenderAdminProduct($event);
    }

    /**
     * new hookpoint for save RelatedProduct.
     *
     * @param EventArgs $event
     */
    public function onAdminProductEditComplete(EventArgs  $event)
    {
        $this->event->onAdminProductEditComplete($event);
    }
}
