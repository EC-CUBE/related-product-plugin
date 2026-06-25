# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## このリポジトリについて

EC-CUBE 4 系の**関連商品プラグイン**。商品詳細ページに関連商品を表示し、管理画面の商品登録・編集画面から商品ごとに関連商品（と説明文）を登録・削除できる。

- 管理画面: 商品登録・編集画面に関連商品フォームを差し込む（`RelatedProductEvent` が `@admin/Product/product.twig` にスニペットを追加、`Controller/Admin/RelatedProductController.php` が商品検索モーダルを担当）
- フロント: 商品詳細画面に関連商品ブロックを表示（`RelatedProductEvent` が `Product/detail.twig` にスニペットを追加）

プラグインコードは `RelatedProduct44`、Composer パッケージ名は `ec-cube/relatedproduct44`。コード中の Twig 名前空間（`@RelatedProduct44`）・クラス名前空間（`Plugin\RelatedProduct44\...`）はすべて `RelatedProduct44` 接頭辞を使う。

### ブランチ運用

ブランチ名が対応する EC-CUBE 本体バージョンを表す（`4.0` / `4.2` / `4.4` など）。`4.2` がデフォルトブランチ。`4.2` ブランチは EC-CUBE 4.2/4.3（`RelatedProduct42`）に対応し、`4.4` ブランチは EC-CUBE 4.4（Symfony 7.4 / Doctrine ORM 3.0 / PHP 8.2+、`RelatedProduct44`）に対応する。**4.3 と 4.4 はアノテーション必須/属性必須・ORM 2/3 の違いで非互換**のため、別ブランチで保守する。

## 開発・テストコマンド

このプラグイン単体では動作せず、**EC-CUBE 本体に組み込んだ状態**で開発・テストする。本体の取得・インストール・プラグイン有効化は `docker-compose.dev.yml` の entrypoint が自動実行する。

```bash
# 開発環境 (SQLite) の起動 — 本体インストール・プラグイン有効化まで自動
export COMPOSE_FILE=docker-compose.yml:docker-compose.dev.yml
docker compose up -d --wait

# MySQL / PostgreSQL で起動する場合
export COMPOSE_FILE=docker-compose.yml:docker-compose.mysql.yml:docker-compose.dev.yml
export COMPOSE_FILE=docker-compose.yml:docker-compose.pgsql.yml:docker-compose.dev.yml

# PHP バージョン切り替え（8.2-apache-4.4 / 8.3-apache-4.4 / 8.4-apache-4.4 / 8.5-apache-4.4）
TAG=8.3-apache-4.4 docker compose up -d --wait
```

起動後は管理画面 `http://localhost:8080/admin`（`admin` / `password`）、メールは MailCatcher `http://localhost:1080`。

### PHPUnit

テストは `Tests/` 配下の PHPUnit（`Tests/Web/`）。`phpunit.xml.dist` により `APP_ENV=test` で実行される。

```bash
docker compose exec ec-cube bash -lc \
  "APP_ENV=test bin/console cache:clear --no-warmup && ./vendor/bin/phpunit -c app/Plugin/RelatedProduct44/phpunit.xml.dist app/Plugin/RelatedProduct44/Tests"
```

**注意（コンパイル済みキャッシュ）**: 有効化したプラグインのルーティングは、コンテナのコンパイル時に `dtb_plugin` を読む `EccubeExtension` で確定する。有効化直後の test キャッシュには反映されていないことがあるため、**PHPUnit 実行前に `APP_ENV=test` でキャッシュをクリアする**。これを怠るとコントローラのルートが `RouteNotFoundException` になる。

### 静的解析・整形（任意）

EC-CUBE 本体（コンテナ内）の vendor を使って実行する。

```bash
# php-cs-fixer
docker compose exec ec-cube bash -lc \
  "cd app/Plugin/RelatedProduct44 && /var/www/html/vendor/bin/php-cs-fixer fix --config=Resource/.php-cs-fixer.dist.php --dry-run --diff"

# rector（再移行・検証用）
docker compose exec ec-cube bash -lc \
  "cd app/Plugin/RelatedProduct44 && /var/www/html/vendor/bin/rector process --config=Resource/rector.php --dry-run"

# phpstan
docker compose exec ec-cube bash -lc \
  "cd app/Plugin/RelatedProduct44 && /var/www/html/vendor/bin/phpstan analyse"
```

phpstan は level 6 で **baseline なし・エラーゼロ**。Repository は `@extends AbstractRepository<RelatedProduct>` を付与して `find()` 等の戻り値型を確定させている。新規コードもこの水準を維持すること。

## アーキテクチャ

- **Entity** (`Entity/RelatedProduct.php`): `plg_related_product` テーブル。`#[ORM\*]` 属性 + 型付きプロパティ。商品（`Product`）に対し親商品（`Product`）と子商品（`ChildProduct`）を ManyToOne で関連付け、`content` に説明文を持つ。
- **EntityExtension** (`Entity/ProductTrait.php`): `Eccube\Entity\Product` に `RelatedProducts`（OneToMany）を拡張追加する。`#[EntityExtension(\Eccube\Entity\Product::class)]` 属性 + `#[ORM\OneToMany]`/`#[ORM\OrderBy]`。
- **Controller** (`Controller/Admin/RelatedProductController.php`): `#[Route]`/`#[Template]` 属性。商品検索モーダル（Ajax）を担当。
- **Form** (`Form/Type/Admin/RelatedProductType.php`): 関連商品 1 件分の入力フォーム。`EntityToIdTransformer` で子商品を ID 連携。
- **FormExtension** (`Form/Extension/Admin/RelatedCollectionExtension.php`): 本体の `ProductType` に `RelatedProducts` コレクションを差し込む。`getExtendedTypes(): iterable` を使用。
- **Event** (`RelatedProductEvent.php`): 管理画面の商品編集画面・フロントの商品詳細画面にテンプレートスニペットを追加。
- **Repository** (`Repository/RelatedProductRepository.php`): `AbstractRepository` を継承。
- **PluginManager** (`PluginManager.php`): デフォルト実装のまま。

## 規約・移行メモ

### 開発ツール設定ファイルは `Resource/` 配下に置く（rector.php / .php-cs-fixer.dist.php）

`rector.php` や `.php-cs-fixer.dist.php` を**プラグインのルート直下に置いてはならない**。`Resource/` 配下に置く。

**理由**: EC-CUBE 本体の `config/eccube/services.yaml` がプラグインを丸ごと PSR-4 サービス検出対象として読み込む:

```yaml
Plugin\:
    resource: '../../../app/Plugin/*'
    exclude: '../../../app/Plugin/*/{Entity,Resource,ServiceProvider,Tests,Codeception,DoctrineMigrations}'
```

ルート直下の `*.php` は「サービスクラス」として読み込まれるため、`rector.php` を置くと Symfony が `Plugin\RelatedProduct44\rector` クラスを期待し、見つからず **EC-CUBE 全体が 500 エラー**になる。`exclude` に `Resource` が含まれるため `Resource/` 配下なら衝突しない。`phpstan.neon.dist` は `.php` ではないためルートに置ける。

**将来「本体に合わせてルートへ戻す」とリグレッションするため、この配置を変更しないこと。**

### docker 環境は `APP_ENV=dev` で起動する

ブラウザログインには実セッション（`session.storage.factory.native`）が必要。`APP_ENV=test` ではモックストレージ（`mock_file`）になりログインできない。また EC-CUBE 4.4（Symfony 7）は既定 `cookie_samesite: none` のため、HTTP 環境では `dockerbuild/dev-framework.yaml`（`cookie_secure:false` / `cookie_samesite:lax`）を `app/config/eccube/packages/dev/framework.yaml` に重ねて回避している。

### プラグイン有効化後は `cache:warmup` まで実行する（TemplateEvent 対策）

本プラグインは `RelatedProductEvent` が `TemplateEvent` で core テンプレート（`@admin/Product/product.twig` / `Product/detail.twig`）にスニペットを注入し、`RelatedCollectionExtension` が `ProductType` を拡張する。これらプラグイン由来のフック／フォーム拡張は、**`eccube:plugin:enable` 直後の 1 回の `cache:clear` では確定しない**（商品編集画面に関連商品フォームが描画されない）。検証の結果、enable とは別パスで **`cache:clear` をもう一度実行**すると確定することがわかった（`enable` が内部で行うキャッシュ再生成と競合するためと見られる）。そのため `docker-compose.dev.yml` の entrypoint は有効化後に `bin/console cache:clear` を **2 回** 実行する。手動でプラグインを再有効化した場合も、`cache:clear` を 2 回（または apache 起動後にもう一度）行うこと。

### プラグインの導入方法（tar + plugin:install）

`docker-compose.dev.yml` はマウントしたプラグインを `./*` で tar 化し `eccube:plugin:install --path` で導入する。`eccube:composer:require` はパッケージ API（`extra.id`）を要求するため path プラグインでは使えない。また **`PharData` は先頭の `./` エントリで展開に失敗する**ため、プラグインディレクトリ内で `./*` を対象に tar 化する（`-C dir .` は不可）。
