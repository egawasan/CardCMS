# CardCMS作業日誌

この日誌はCardCMS開発の記録である。

Version1.0からGitHubによるバージョン管理を開始し、
WordPressサイト（https://ichiba-k.jp）のCardCMSへの移行を目標として、
日々の開発内容を記録する。

---

## 2026-07-14

### Version 1.3 開始

【本日の作業】

・PROJECT_STATUS.md を確認
・現在のWordPressトップページを確認
・トップページの大きな構成を整理
・docs/005_WordPressトップページ移植メモ.md を作成

【成果】

・Version 1.3 で移植する対象を整理できた
・いきなり実装せず、まず移植方針を確認できる状態にした

【次回予定】

・ロゴ画像の表示方法を決める
・メイン画像の表示方法を決める
・トップ紹介文を固定HTMLとして移植する

---

### Version 1.2 完了

【本日の作業】

・PROJECT_STATUS.md を基準に現在の状態を整理
・公開ページを index.php として表示確認
・header.php と footer.php への分離を整理
・管理画面の公開ページリンクを index.php に変更
・CHANGELOG.md に Version 1.2 を追加

【成果】

・公開ページをPHPベースで管理する第一段階が完了
・今後、WordPressトップページ移植へ進む準備ができた

【次回予定】

・WordPressトップページの構造確認
・ヘッダー、メニュー、フッターの移植方針を決める

---

## 2026-07-13

### Version 1.2 開始

【本日の作業】

・public/index.html → index.php へ変更
・public/includes フォルダ作成
・header.php 作成
・footer.php 作成
・共通レイアウト化開始

【成果】

・CardCMSをPHPベースのCMS構造へ移行開始

【次回予定】

・header.php 完成
・footer.php 完成
・index.php 整理
・表示確認
・GitHubへ Version 1.2 をコミット

【補足】

・Version 1.1 は「カード番号を整備」としてGitHubへ保存済み
・公開ページのPHP化は Version 1.2 として進める

---

開発体制

プロジェクトオーナー
江川清男

設計・開発
江川清男

開発支援
ChatGPT
