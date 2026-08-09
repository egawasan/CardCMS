<?php

require_once __DIR__ . "/../includes/auth.php";

cardcms_require_admin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../admin/index.php");
    exit;
}

date_default_timezone_set("Asia/Tokyo");

function parseAdditionalImages($value)
{
    $lines = preg_split("/\R/", (string)$value);
    $images = [];

    foreach ($lines as $line) {
        $image = trim($line);

        if ($image !== "") {
            $images[] = $image;
        }
    }

    return array_values(array_unique($images));
}

function parseAdditionalUploadedImages($values)
{
    if (!is_array($values)) {
        return [];
    }

    $images = [];

    foreach ($values as $value) {
        $filename = basename(trim((string)$value));

        if ($filename !== "" && isAllowedUploadedImage($filename)) {
            $images[] = $filename;
        }
    }

    return array_values(array_unique($images));
}

function parseCardImagesOrder($value)
{
    $lines = preg_split("/\R/", (string)$value);
    $images = [];

    foreach ($lines as $line) {
        $filename = basename(trim($line));

        if ($filename !== "" && isAllowedUploadedImage($filename)) {
            $images[] = $filename;
        }
    }

    return array_values(array_unique($images));
}

function isAllowedUploadedImage($filename)
{
    $filename = basename((string)$filename);

    if ($filename === "") {
        return false;
    }

    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($extension, ["jpg", "jpeg", "png", "gif", "webp"], true)) {
        return false;
    }

    return is_file(__DIR__ . "/../uploads/" . $filename);
}

$id = $_POST['id'];
$title = $_POST['title'];
$card_type = $_POST['card_type'] ?? 'お知らせ';
$layout = $_POST['layout'] ?? 'Text';
$slug = trim($_POST['slug'] ?? '');
$category = $_POST['category'];
$body = $_POST['body'];
$sort_order = $_POST['sort_order'];
$published = isset($_POST['published']);
$has_card_images_order = array_key_exists('card_images_order', $_POST);
$card_images_order = parseCardImagesOrder($_POST['card_images_order'] ?? "");
$uploaded_image = trim($_POST['uploaded_image'] ?? "");
$manual_additional_images = parseAdditionalImages($_POST['additional_images'] ?? "");
$additional_images = $manual_additional_images;
$now = date("Y-m-d H:i:s");

// 現在の画像
$image = $_POST['current_image'] ?? "";

// 新しい画像選択画面では、1枚目を代表画像、2枚目以降を追加画像として保存する
if ($has_card_images_order) {
    $image = $card_images_order[0] ?? "";
    $additional_images = array_values(array_unique(array_merge(
        array_slice($card_images_order, 1),
        $manual_additional_images
    )));
} else {
    $additional_images = array_values(array_unique(array_merge(
        parseAdditionalUploadedImages($_POST['additional_uploaded_images'] ?? []),
        $manual_additional_images
    )));

    // 旧方式: 画像管理でアップロード済みの画像を選んだ場合
    if ($uploaded_image === "__none__") {
        $image = "";
    } elseif ($uploaded_image !== "" && isAllowedUploadedImage($uploaded_image)) {
        $image = basename($uploaded_image);
    }
}

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
        $card['slug'] = $slug;
        $card['title'] = $title;
        $card['category'] = $category;
        $card['body'] = $body;
        $card['image'] = $image;
        $card['images'] = $additional_images;
        $card['sort_order'] = (int)$sort_order;
        $card['published'] = $published;

        if (empty($card['created_at'])) {
            $card['created_at'] = $now;
        }

        $card['updated_at'] = $now;

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
