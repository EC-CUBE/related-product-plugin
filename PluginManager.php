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
use Eccube\Plugin\AbstractPluginManager;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class PluginManager.
 */
class PluginManager extends AbstractPluginManager
{
    /**
     * @var string コピー元リソースディレクトリ
     */
    private $origin;

    /**
     * @var string コピー先リソースディレクトリ
     */
    private $target;

    public function __construct()
    {
        // コピー元のディレクトリ
        $this->origin = __DIR__.'/Resource/assets';
        // コピー先のディレクトリ
        $this->target = '/relatedproduct';
    }

    /**
     * プラグインインストール時の処理.
     *
     * @param array       $config
     * @param Application $app
     *
     * @throws \Exception
     */
    public function install($config, $app)
    {
        // リソースファイルのコピー
        $this->copyAssets($app);
    }

    /**
     * プラグイン削除時の処理.
     *
     * @param array       $config
     * @param Application $app
     */
    public function uninstall($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code'], 0);

        // リソースファイルの削除
        $this->removeAssets($app);
    }

    /**
     * プラグイン有効時の処理.
     *
     * @param array       $config
     * @param Application $app
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
     * @param array       $config
     * @param Application $app
     */
    public function disable($config, $app)
    {
    }

    /**
     * プラグイン更新時の処理.
     *
     *  @param array       $config
     *  @param Application $app
     */
    public function update($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code']);
        
        // リソースファイルのコピー
        $this->copyAssets($app);
    }

    /**
     * リソースファイル等をコピー
     *
     * @param Application $app
     */
    private function copyAssets(Application $app)
    {
        $file = new Filesystem();
        $file->mirror($this->origin, $app['config']['root_dir'].'/html/plugin'.$this->target.'/assets');
    }

    /**
     * コピーしたリソースファイルなどを削除.
     *
     * @param Application $app
     */
    private function removeAssets(Application $app)
    {
        $file = new Filesystem();
        $file->remove($app['config']['root_dir'].'/html/plugin'.$this->target);
    }
}
