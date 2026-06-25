# 関連商品プラグイン

[![CI for RelatedProduct44](https://github.com/EC-CUBE/related-product-plugin/actions/workflows/main.yml/badge.svg)](https://github.com/EC-CUBE/related-product-plugin/actions/workflows/main.yml)

## 概要
商品詳細ページに、関連する商品を表示できるようになるプラグイン。

## フロント
### 商品詳細ページに、関連する商品を表示することができる。
- 商品詳細ページに、関連する商品の写真と情報を表示する。
- 説明文が登録されている場合は、説明文も表示する。
- クリックするとその商品のページに異動することができる。

## 管理
### 商品ごとに、任意の商品を関連付けをすることができる。
- 商品詳細ページに、関連する商品を登録するためのフォームを表示する。
- 関連付けする商品を選択するために、商品検索ダイアログを利用できる。

### 関連付けした商品ごとに、商品をアピールする説明文を記入することができる。
- 関連付けした商品ごとに、テキストの入力フォームを表示する。
- HTMLタグが使用可能。

### 商品の関連付けを削除することができる。
- 関連付けした商品がある場合、削除ボタンを利用できるようにする。

----------------------------------------------------------------------
## Docker Compose でのテスト

Docker Compose で EC-CUBE 4.4 + 本プラグインの環境を起動し、PHPUnit を実行できます。
EC-CUBE 本体は初回起動時に自動インストールされ（デモ商品データも投入）、マウントした
プラグインが自動でインストール・有効化されます。

### 構成ファイル

| ファイル | 役割 |
|---|---|
| `docker-compose.yml` | ベース（EC-CUBE 4.4 + mailcatcher、SQLite） |
| `docker-compose.dev.yml` | プラグインのマウント・インストール・有効化 |
| `docker-compose.mysql.yml` | DB を MySQL 8 に切り替え |
| `docker-compose.pgsql.yml` | DB を PostgreSQL 18 に切り替え |

### 環境の起動

```bash
# SQLite で起動
export COMPOSE_FILE=docker-compose.yml:docker-compose.dev.yml
docker compose up -d --wait

# MySQL で起動する場合
export COMPOSE_FILE=docker-compose.yml:docker-compose.mysql.yml:docker-compose.dev.yml
docker compose up -d --wait

# PostgreSQL で起動する場合
export COMPOSE_FILE=docker-compose.yml:docker-compose.pgsql.yml:docker-compose.dev.yml
docker compose up -d --wait
```

PHP バージョンは環境変数 `TAG` で変更できます（`8.2-apache-4.4` / `8.3-apache-4.4` / `8.4-apache-4.4` / `8.5-apache-4.4`）。

```bash
TAG=8.3-apache-4.4 docker compose up -d --wait
```

### PHPUnit の実行

PHPUnit は `phpunit.xml.dist` により `APP_ENV=test` で実行されます。有効化直後は test 環境の
コンパイル済みキャッシュにプラグインのルーティングが反映されていない場合があるため、
実行前に test 環境のキャッシュをクリアします。

```bash
docker compose exec ec-cube bash -lc \
  "APP_ENV=test bin/console cache:clear --no-warmup && ./vendor/bin/phpunit -c app/Plugin/RelatedProduct44/phpunit.xml.dist app/Plugin/RelatedProduct44/Tests"
```

管理画面は http://localhost:8080/admin 、送信メールは http://localhost:1080 (mailcatcher) で確認できます。

### 環境の破棄

```bash
docker compose down -v
```

