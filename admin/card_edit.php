<?php

require_once __DIR__ . "/../includes/auth.php";

cardcms_require_admin();

$id = $_GET['id'] ?? "";

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function imageSrc($imagePath)
{
    if (preg_match("/^https?:\/\//", (string)$imagePath)) {
        return $imagePath;
    }

    return "../uploads/" . rawurlencode((string)$imagePath);
}

// cards.json を読み込む
$cards = json_decode(
    file_get_contents("../database/cards.json"),
    true
);

if (!is_array($cards)) {
    $cards = [];
}

// 編集するカードを探す
$editCard = null;

foreach ($cards as $card) {

    if ($card['id'] == $id) {
        $editCard = $card;
        break;
    }

}

// 見つからなければ終了
if ($editCard === null) {
    die("カードが見つかりません。");
}

$cardNumber = $editCard['card_number'] ?? "";
$cardType = $editCard['card_type'] ?? "お知らせ";
$layout = $editCard['layout'] ?? "Text";
$slug = $editCard['slug'] ?? "";
$createdAt = $editCard['created_at'] ?? "未記録";
$updatedAt = $editCard['updated_at'] ?? "未記録";
$additionalImages = $editCard['images'] ?? [];

if (!is_array($additionalImages)) {
    $additionalImages = [];
}

$cardTypeOptions = ["トップページ", "お知らせ", "サービス", "会社情報", "お問い合わせ", "採用"];
$layoutOptions = ["Hero", "Text", "ImageLeft", "ImageRight", "Gallery", "Contact"];

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>CardCMS - カード編集</title>

<style>

*{
	box-sizing:border-box;
}

body{
	margin:0;
	font-family:-apple-system,BlinkMacSystemFont,"Helvetica Neue",sans-serif;
	background:#eef2f7;
}

header{
	background:#2c3e50;
	color:white;
	padding:18px;
	font-size:24px;
	text-align:center;
	font-weight:bold;
}

.container{
	max-width:900px;
	margin:30px auto;
	background:white;
	padding:30px;
	border-radius:12px;
	box-shadow:0 5px 15px rgba(0,0,0,.15);
}

h2{
	margin-top:0;
	color:#2c3e50;
}

.row{
	margin-bottom:20px;
}

label{
	display:block;
	font-weight:bold;
	margin-bottom:8px;
	color:#555;
}

.help-text{
	margin:6px 0 10px;
	color:#777;
	font-size:14px;
	line-height:1.6;
}

input[type=text],
input[type=number],
select,
textarea{

	width:100%;
	padding:12px;
	border:1px solid #ccc;
	border-radius:6px;
	font-size:16px;

}

textarea{

	min-height:220px;
	resize:vertical;

}

.small-textarea{
	min-height:120px;
}

.image-box{

	border:2px dashed #bbb;
	border-radius:10px;
	padding:40px;
	text-align:center;
	color:#999;

}

.button-area{

	text-align:center;
	margin-top:40px;

}

button{

	padding:14px 40px;
	font-size:18px;
	border:none;
	border-radius:8px;
	cursor:pointer;
	margin:10px;

}

.save{

	background:#3498db;
	color:white;

}

.save:hover{

	background:#2980b9;

}

.cancel{

	background:#ddd;

}

.card-number{

	font-size:26px;
	color:#3498db;
	font-weight:bold;

}

.info-text{
	padding:12px;
	border:1px solid #ddd;
	border-radius:6px;
	background:#f6f8fb;
	color:#555;
}

footer{

	text-align:center;
	color:#888;
	margin:30px;

}

</style>

</head>

<body>

<header>

CardCMS 管理画面

</header>

<div class="container">
	<form action="../api/update_card.php"
      method="post"
      enctype="multipart/form-data">

    <input
    type="hidden"
    name="id"
    value="<?php echo h($editCard['id'] ?? ""); ?>">

<h2>📄 カード編集</h2>

<div class="row">

<label>カード番号</label>

<div class="card-number">

<?php echo h($cardNumber); ?>

</div>

</div>

<div class="row">

<label>カード種別</label>

<select name="card_type">

<?php foreach ($cardTypeOptions as $option): ?>

<option value="<?php echo h($option); ?>" <?php if ($cardType === $option) echo "selected"; ?>>
    <?php echo h($option); ?>
</option>

<?php endforeach; ?>

</select>

</div>

<div class="row">

<label>レイアウト</label>

<select name="layout">

<?php foreach ($layoutOptions as $option): ?>

<option value="<?php echo h($option); ?>" <?php if ($layout === $option) echo "selected"; ?>>
    <?php echo h($option); ?>
</option>

<?php endforeach; ?>

</select>

</div>

<div class="row">

<label>管理名（slug）</label>

<input
    type="text"
    name="slug"
    value="<?php echo h($slug); ?>"
    placeholder="例: main-visual">

</div>

<div class="row">

<label>作成日時</label>

<div class="info-text">

<?php echo h($createdAt); ?>

</div>

</div>

<div class="row">

<label>更新日時</label>

<div class="info-text">

<?php echo h($updatedAt); ?>

</div>

</div>

<div class="row">

<label>タイトル</label>

<input
    type="text"
    name="title"
    value="<?php echo h($editCard['title'] ?? ""); ?>"
    required>

</div>

<div class="row">

<label>カテゴリー</label>

<select name="category">

<option value="お知らせ" <?php if(($editCard['category'] ?? "")=="お知らせ") echo "selected"; ?>>
    お知らせ
</option>

<option value="施工事例" <?php if(($editCard['category'] ?? "")=="施工事例") echo "selected"; ?>>
    施工事例
</option>

<option value="商品紹介" <?php if(($editCard['category'] ?? "")=="商品紹介") echo "selected"; ?>>
    商品紹介
</option>

<option value="その他" <?php if(($editCard['category'] ?? "")=="その他") echo "selected"; ?>>
    その他
</option>

</select>

</div>

<div class="row">

<label>本文</label>

<div class="help-text">
普通の文章はそのまま、箇条書きは「・」から、住所や電話は「項目: 内容」の形で入力します。
</div>

<textarea
    name="body"><?php echo h($editCard['body'] ?? ""); ?></textarea>

</div>

<div class="row">

<label>現在の画像</label><br>

<?php if (!empty($editCard['image'])): ?>

<img
    src="<?php echo h(imageSrc($editCard['image'])); ?>"
    width="200">

<?php else: ?>

画像は登録されていません

<?php endif; ?>

</div>

<div class="row">

<label>新しい画像</label><br>

<input type="file" name="image">

<input
	type="hidden"
	name="current_image"
	value="<?php echo h($editCard['image'] ?? ""); ?>">

</div>

<div class="row">

<label>追加画像（1行に1つ）</label>

<textarea
    name="additional_images"
    class="small-textarea"
    placeholder="例: https://example.com/image.jpg"><?php echo h(implode("\n", $additionalImages)); ?></textarea>

</div>

<div class="row">

<label>表示順</label>

<input
    type="number"
    name="sort_order"
    value="<?php echo h($editCard['sort_order'] ?? 10); ?>">

</div>

<div class="row">

<label>

<input
	type="checkbox"
	name="published"
	value="1"
	<?php if (!empty($editCard['published'])) echo "checked"; ?>>

公開する

</label>

</div>

<div class="button-area">

<button type="submit" class="save">💾 保存</button>

<button type="button" class="cancel" onclick="location.href='card_list.php'">戻る</button>

</div>

</form>

</div>

<footer>

CardCMS Version 1.3

</footer>

</body>
</html>
