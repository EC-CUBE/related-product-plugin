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
use Symfony\Component\Filesystem\Filesystem;

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

    /**
     * PluginManager constructor.
     */
    public function __construct()
    {
        // コピー元のディレクトリ
        $this->origin = __DIR__.'/Resource/assets';
        // コピー先のディレクトリ
        $this->target = __DIR__ . '/../../../html/plugin/relatedproduct';
    }

    /**
     * プラグインインストール時の処理
     *
     * @param $config
     * @param $app
     * @throws \Exception
     */
    public function install($config, $app)
    {
        // リソースファイルのコピー
        $this->copyAssets($app);
    }

    /**
     * プラグイン削除時の処理
     *
     * @param $config
     * @param $app
     */
    public function uninstall($config, $app)
    {
        // リソースファイルの削除
        $this->removeAssets($app);
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code'], 0);
    }

    /**
     * プラグイン有効時の処理
     *
     * @param $config
     * @param $app
     * @throws \Exception
     */
    public function enable($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code']);
    }

    /**
     * プラグイン無効時の処理
     *
     * @param $config
     * @param $app
     */
    public function disable($config, $app)
    {
    }

    /**
     * プラグイン更新時の処理
     * @param $config
     * @param $app
     */
    public function update($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code'], 0);
    }

    /**
     * リソースファイル等をコピー
     *
     * @param $app
     */
    private function copyAssets($app)
    {
        $file = new Filesystem();
        $file->mirror($this->origin, $this->target.'/assets');
    }

    /**
     * コピーしたリソースファイルなどを削除
     *
     * @param $app
     */
    private function removeAssets($app)
    {
        $file = new Filesystem();
        $file->remove($this->target);
    }
}