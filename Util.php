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

use Eccube\Common\Constant;

/**
 * Class Util.
 */
class Util
{
    /**
     * Version compare util function.
     *
     * @return bool
     */
    public static function isSupportNewHookpoint()
    {
        //current version >= 3.0.9
        if (version_compare(Constant::VERSION, '3.0.9', '>=')) {
            return true;
        } else {
            return false;
        }
    }
}
