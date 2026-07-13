<?php

$id = $_GET['id'];

// cards.json を読み込む
$cards = json_decode(
    file_get_contents("../database/cards.json"),
    true
);

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

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>CardCMS - 新しいカード</title>

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
    value="<?php echo $editCard['id']; ?>">

<h2>📄 新しいカード</h2>

<div class="row">

<label>カード番号</label>

<div class="card-number">

000001

</div>

</div>

<div class="row">

<label>タイトル</label>

<input
    type="text"
    name="title"
    value="<?php echo htmlspecialchars($editCard['title']); ?>"
    required>

</div>

<div class="row">

<label>カテゴリー</label>

<select name="category">

<option value="お知らせ" <?php if($editCard['category']=="お知らせ") echo "selected"; ?>>
    お知らせ
</option>

<option value="施工事例" <?php if($editCard['category']=="施工事例") echo "selected"; ?>>
    施工事例
</option>

<option value="商品紹介" <?php if($editCard['category']=="商品紹介") echo "selected"; ?>>
    商品紹介
</option>

<option value="その他" <?php if($editCard['category']=="その他") echo "selected"; ?>>
    その他
</option>

</select>

</div>

<div class="row">

<label>本文</label>

<textarea
    name="body"><?php echo htmlspecialchars($editCard['body']); ?></textarea>

</div>

<div class="row">

<label>現在の画像</label><br>

<?php if (!empty($editCard['image'])): ?>

<img
    src="../uploads/<?php echo htmlspecialchars($editCard['image']); ?>"
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
	value="<?php echo htmlspecialchars($editCard['image'] ?? ''); ?>">

</div>

<div class="row">

<label>表示順</label>

<input
    type="number"
    name="sort_order"
    value="<?php echo $editCard['sort_order']; ?>">

</div>

<div class="row">

<label>

<input
	type="checkbox"
	name="published"
	value="1"
	checked>

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

CardCMS Version 0.13

</footer>

</body>
</html>
