<?php

$id = $_POST['id'];
$title = $_POST['title'];
$card_type = $_POST['card_type'] ?? 'お知らせ';
$layout = $_POST['layout'] ?? 'Text';
$category = $_POST['category'];
$body = $_POST['body'];
$sort_order = $_POST['sort_order'];
$published = isset($_POST['published']);

// 現在の画像
$image = $_POST['current_image'];

// 新しい画像が選択された場合
if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {

    $filename = time() . "_" . basename($_FILES["image"]["name"]);

    move_uploaded_file(
        $_FILES["image"]["tmp_name"],
        "../uploads/" . $filename
    );

    $image = $filename;
}

// cards.json を読み込む
$cards = json_decode(
    file_get_contents("../database/cards.json"),
    true
);

// カードを書き換える
foreach ($cards as &$card) {

    if ($card['id'] == $id) {

        $card['card_type'] = $card_type;
        $card['layout'] = $layout;
        $card['title'] = $title;
        $card['category'] = $category;
        $card['body'] = $body;
        $card['image'] = $image;
        $card['sort_order'] = (int)$sort_order;
        $card['published'] = $published;

        break;

    }

}

file_put_contents(
    "../database/cards.json",
    json_encode($cards, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo "<h1>更新しました！</h1>";

echo '<p><a href="../admin/card_list.php">一覧へ戻る</a></p>';

?>
