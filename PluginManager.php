<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RelatedProduct;

use Eccube\Application;
use Eccube\Plugin\AbstractPluginManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;

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

    /**
     * PluginManager constructor.
     */
    public function __construct()
    {
        // コピー元のディレクトリ
        $this->origin = __DIR__.DIRECTORY_SEPARATOR.'Resource'.DIRECTORY_SEPARATOR.'assets';
        // コピー先のディレクトリ
        $this->target = 'relatedproduct';
    }

    /**
     * プラグインインストール時の処理.
     *
     * @param array $config
     * @param Application $app
     * @param ContainerInterface $container
     *
     * @throws \Exception
     */
    public function enable($config = [], Application $app = null, ContainerInterface $container)
    {
        // リソースファイルのコピー
        $this->copyAssets($container);
    }

    /**
     * プラグイン削除時の処理.
     *
     * @param array $config
     * @param Application $app
     * @param ContainerInterface $container
     */
    public function uninstall($config = [], Application $app = null, ContainerInterface $container)
    {
        // リソースファイルの削除
        $this->removeAssets($container);
    }

    /**
     * プラグイン更新時の処理.
     *
     * @param array $config
     * @param Application $app
     * @param ContainerInterface $container
     */
    public function update($config = [], Application $app = null, ContainerInterface $container)
    {
        // リソースファイルのコピー
        $this->copyAssets($container);
    }

    /**
     * リソースファイル等をコピー
     *
     * @param ContainerInterface $container
     */
    private function copyAssets(ContainerInterface $container)
    {
        $file = new Filesystem();
        $file->mirror($this->origin, $this->getAssetPath($container));
    }

    /**
     * コピーしたリソースファイルなどを削除.
     *
     * @param ContainerInterface $container
     */
    private function removeAssets(ContainerInterface $container)
    {
        $file = new Filesystem();
        $file->remove($this->getAssetPath($container));
    }

    /**
     * Get asset path which need to copy to
     *
     * @param ContainerInterface $container
     *
     * @return string
     */
    public function getAssetPath(ContainerInterface $container)
    {
        var_dump($container->getParameter('kernel.project_dir'));
        var_dump(__DIR__);
        var_dump($_ENV);
        var_dump(getenv('kernel.project_dir'));
        $projectDir = $container->getParameter('kernel.project_dir');
        /** @var Packages $packages */
        $packages = $container->get('assets.packages');
        /** @var PathPackage $package */
        $package = $packages->getPackage('plugin');

        return $projectDir.$package->getBasePath().$this->target.DIRECTORY_SEPARATOR.'assets';
    }
}
