/*
 * This file is part of the Related Product plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$(function(){
    $('.related_product_carousel').slick({
        infinite: false,
        speed: 300,
        prevArrow:'<button type="button" class="slick-prev"><span class="angle-circle"><svg class="cb cb-angle-right"><use xlink:href="#cb-angle-right" /></svg></span></button>',
        nextArrow:'<button type="button" class="slick-next"><span class="angle-circle"><svg class="cb cb-angle-right"><use xlink:href="#cb-angle-right" /></svg></span></button>',
        slidesToShow: 4,   // 一画面に表示する関連商品の数
        slidesToScroll: 4, // スクロール時に移動する関連商品の数
        responsive: [
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 3,  // (スマホ画面)一画面に表示する関連商品の数
                    slidesToScroll: 3 // (スマホ画面)スクロール時に移動する関連商品の数
                }
            }
        ]
    });
});
