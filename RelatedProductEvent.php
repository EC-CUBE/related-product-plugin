<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * https://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RelatedProduct44;

use Eccube\Event\TemplateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RelatedProductEvent implements EventSubscriberInterface
{
    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            '@admin/Product/product.twig' => 'onRenderAdminProduct',
            'Product/detail.twig' => 'onRenderProductDetail',
        ];
    }

    /**
     * フロント：商品詳細画面に関連商品を表示する.
     *
     * @param TemplateEvent $event
     */
    public function onRenderProductDetail(TemplateEvent $event): void
    {
        $event->addSnippet('@RelatedProduct44/front/related_product.twig');
    }

    /**
     * 管理画面：商品登録画面に関連商品登録フォームを表示する.
     *
     * @param TemplateEvent $event
     */
    public function onRenderAdminProduct(TemplateEvent $event): void
    {
        $event->addSnippet('@RelatedProduct44/admin/related_product.twig');
    }
}
