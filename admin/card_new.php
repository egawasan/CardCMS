<?php

$cards = json_decode(file_get_contents("../database/cards.json"), true);

if (!is_array($cards)) {
	$cards = [];
}

$nextNumber = count($cards) + 1;

foreach ($cards as $card) {
	$cardNumber = $card['card_number'] ?? "";

	if ($cardNumber !== "" && ctype_digit((string)$cardNumber)) {
		$nextNumber = max($nextNumber, ((int)$cardNumber) + 1);
	}
}

$nextCardNumber = str_pad((string)$nextNumber, 6, "0", STR_PAD_LEFT);

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
	<form action="../api/save_card.php"
		method="post"
		enctype="multipart/form-data">

<h2>📄 新しいカード</h2>

<div class="row">

<label>カード番号</label>

<div class="card-number">

<?php echo htmlspecialchars($nextCardNumber, ENT_QUOTES, "UTF-8"); ?>

</div>

</div>

<div class="row">

<label>カード種別</label>

<select name="card_type">

<option>トップページ</option>

<option>お知らせ</option>

<option>サービス</option>

<option>会社情報</option>

<option>お問い合わせ</option>

<option>採用</option>

</select>

</div>

<div class="row">

<label>レイアウト</label>

<select name="layout">

<option>Hero</option>

<option>Text</option>

<option>ImageLeft</option>

<option>ImageRight</option>

<option>Gallery</option>

<option>Contact</option>

</select>

</div>

<div class="row">

<label>タイトル</label>

<input
	type="text"
	name="title"
	placeholder="タイトルを入力"
	required>

</div>

<div class="row">

<label>カテゴリー</label>

<select name="category">

<option>お知らせ</option>

<option>施工事例</option>

<option>商品紹介</option>

<option>その他</option>

</select>

</div>

<div class="row">

<label>本文</label>

<textarea
	name="body"
	placeholder="本文を入力してください"></textarea>

</div>

<div class="row">

<label>画像</label>

<div class="image-box">

📷

<br><br>

ここへ画像をドラッグ＆ドロップ

<br>

または

<br><br>

<input type="file" name="image">

</div>

</div>

<div class="row">

<label>表示順</label>

<input
	type="number"
	name="sort_order"
	value="10">

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

CardCMS Version 1.3

</footer>

</body>
</html>
