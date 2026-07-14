# CardCMS

CardCMS は、カード型の情報を登録・編集・公開するための小さなCMSです。

## 現在のバージョン

CardCMS Version 1.2

## 起動方法

デスクトップの `CardCMS起動.command` を開きます。

起動後、次の管理メニューが開きます。

http://localhost:8000/admin/index.php

## できること

- 新しいカードの登録
- カード番号の自動採番
- カード一覧の表示
- カード内容の編集
- カードの削除
- 画像アップロード
- 編集画面での画像差し替え
- 画像管理画面でのアップロード済み画像確認
- 公開ページへのカード表示
- システム情報の確認

## 主な画面

- 管理メニュー: `admin/index.php`
- 新しいカード: `admin/card_new.php`
- カード一覧: `admin/card_list.php`
- 画像管理: `admin/image_manager.php`
- システム情報: `admin/settings.php`
- 公開ページ: `public/index.php`

## データ保存場所

カードデータは次のファイルに保存します。

`database/cards.json`

アップロード画像は次のフォルダに保存します。

`uploads/`

## 現在の区切り

Version 1.2 では、公開ページをPHP化し、header.php と footer.php に分けて管理できる形に整えています。

今後は、ログイン機能、公開ページのデザイン強化、画像削除、検索・カテゴリ絞り込みなどを追加できます。
