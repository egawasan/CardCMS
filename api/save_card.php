<?php

date_default_timezone_set("Asia/Tokyo");

// フォームから送られてきたデータを変数に入れる
$title = $_POST['title'];
$card_type = $_POST['card_type'] ?? 'お知らせ';
$layout = $_POST['layout'] ?? 'Text';
$slug = trim($_POST['slug'] ?? '');
$category = $_POST['category'];
$body = $_POST['body'];
$sort_order = $_POST['sort_order'];
$published = isset($_POST['published']);

// 現在のカード一覧を読み込む
$cards = json_decode(file_get_contents("../database/cards.json"), true);

// 配列でなければ空配列にする
if (!is_array($cards)) {
    $cards = [];
}

$nextNumber = count($cards) + 1;

foreach ($cards as $existingCard) {
    $cardNumber = $existingCard['card_number'] ?? "";

    if ($cardNumber !== "" && ctype_digit((string)$cardNumber)) {
        $nextNumber = max($nextNumber, ((int)$cardNumber) + 1);
    }
}

$nextCardNumber = str_pad((string)$nextNumber, 6, "0", STR_PAD_LEFT);
$now = date("Y-m-d H:i:s");

// 画像アップロード
$image = "";

if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {

    $filename = time() . "_" . basename($_FILES["image"]["name"]);

    move_uploaded_file(
        $_FILES["image"]["tmp_name"],
        "../uploads/" . $filename
    );

    $image = $filename;
}

//カード1枚分のデータを作る
$card = [
    "id" => time(),
    "card_number" => $nextCardNumber,
    "card_type" => $card_type,
    "layout" => $layout,
    "slug" => $slug,
    "title" => $title,
    "category" => $category,
    "body" => $body,
    "image" => $image,
    "sort_order" => (int)$sort_order,
    "published" => $published,
    "created_at" => $now,
    "updated_at" => $now
];

// 新しいカードを追加
$cards[] = $card;

//  JSONファイルへ保存
file_put_contents(
    "../database/cards.json",
    json_encode($cards, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo "<h1>保存しました！</h1>";
echo "<p>カードが正常に登録されました。</p>";
