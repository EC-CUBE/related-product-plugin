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

namespace Plugin\RelatedProduct;

use Eccube\Plugin\AbstractPluginManager;

/**
 * Class PluginManager.
 */
class PluginManager extends AbstractPluginManager
{
    /**
     * PluginManager constructor.
     */
    public function __construct()
    {
    }

    /**
     * プラグインインストール時の処理.
     *
     * @param $config
     * @param $app
     *
     * @throws \Exception
     */
    public function install($config, $app)
    {
    }

    /**
     * プラグイン削除時の処理.
     *
     * @param $config
     * @param $app
     */
    public function uninstall($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code'], 0);
    }

    /**
     * プラグイン有効時の処理.
     *
     * @param $config
     * @param $app
     *
     * @throws \Exception
     */
    public function enable($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code']);
    }

    /**
     * プラグイン無効時の処理.
     *
     * @param $config
     * @param $app
     */
    public function disable($config, $app)
    {
    }

    /**
     * プラグイン更新時の処理.
     *
     * @param $config
     * @param $app
     */
    public function update($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code']);
    }
}
