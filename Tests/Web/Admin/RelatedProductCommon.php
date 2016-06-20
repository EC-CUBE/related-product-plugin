<?php
/**
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RelatedProduct\Tests\Web\Admin;


use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;

class RelatedProductCommon extends AbstractAdminWebTestCase
{
    protected function createProductFormData()
    {
        $faker = $this->getFaker();
        $form = array(
            'class' => array(
                'product_type' => 1,
                'price01' => $faker->randomNumber(5),
                'price02' => $faker->randomNumber(5),
                'stock' => $faker->randomNumber(3),
                'stock_unlimited' => 0,
                'code' => $faker->word,
            ),
            'name' => $faker->word,
            'description_detail' => $faker->text,
            'search_word' => $faker->word,
            'free_area' => $faker->text,
            'Status' => 1,
            'note' => $faker->text,
            '_token' => 'dummy',
        );
        $form['related_collection'][0]['ChildProduct'] = 1;
        $form['related_collection'][0]['content'] = $faker->text;
        return $form;
    }
}