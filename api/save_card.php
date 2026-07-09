<?php

// フォームから送られてきたデータを変数に入れる
$title = $_POST['title'];
$category = $_POST['category'];
$body = $_POST['body'];
$sort_order = $_POST['sort_order'];
$published = isset($_POST['published']);

//カード1枚分のデータを作る
$card = [
    "id" => time(),
    "title" =>$title,
    "category" => $category,
    "body" => $body,
    "sort_order" => (int)$sort_order,
    "published" => $published
];

// 現在のカード一覧を読み込む
$cards = json_decode(file_get_contents("../database/cards.json"), true);

// 配列でなければ空配列にする
if (!is_array($cards)) {
    $cards = [];
}

// 新しいカードを追加
$cards[] = $card;

//  JSONファイルへ保存
file_put_contents(
    "../database/cards.json",
    json_encode($cards, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo "<h1>保存しました！</h1>";
echo "<p>カードが正常に登録されました。</p>";
