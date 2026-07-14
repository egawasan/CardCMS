<?php

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$cards = json_decode(
    file_get_contents("../database/cards.json"),
    true
);

if (!is_array($cards)) {
    $cards = [];
}

$uploadFiles = glob("../uploads/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);

if ($uploadFiles === false) {
    $uploadFiles = [];
}

$publishedCount = 0;

foreach ($cards as $card) {
    if (!empty($card['published'])) {
        $publishedCount++;
    }
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>CardCMS - システム情報</title>

<style>

body{
    margin:0;
    font-family:-apple-system,BlinkMacSystemFont,"Helvetica Neue",sans-serif;
    background:#eef2f7;
    color:#2c3e50;
}

header{
    background:#2c3e50;
    color:white;
    padding:20px;
    text-align:center;
    font-size:28px;
    font-weight:bold;
}

.container{
    max-width:850px;
    margin:30px auto;
    background:white;
    padding:30px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}

h2{
    margin-top:0;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

th,
td{
    text-align:left;
    padding:14px;
    border-bottom:1px solid #d9e0e7;
}

th{
    width:34%;
    color:#4f5f6f;
    background:#f7f9fb;
}

.button-area{
    margin-top:28px;
}

.button{
    display:inline-block;
    background:#3498db;
    color:white;
    text-decoration:none;
    padding:12px 20px;
    border-radius:8px;
}

.button:hover{
    background:#2980b9;
}

footer{
    text-align:center;
    color:#888;
    margin:30px;
}

</style>
</head>

<body>

<header>CardCMS 管理画面</header>

<div class="container">

<h2>システム情報</h2>

<table>
    <tr>
        <th>バージョン</th>
        <td>CardCMS Version 1.3</td>
    </tr>
    <tr>
        <th>PHPバージョン</th>
        <td><?php echo h(PHP_VERSION); ?></td>
    </tr>
    <tr>
        <th>カード件数</th>
        <td><?php echo count($cards); ?> 件</td>
    </tr>
    <tr>
        <th>公開中カード</th>
        <td><?php echo $publishedCount; ?> 件</td>
    </tr>
    <tr>
        <th>アップロード画像</th>
        <td><?php echo count($uploadFiles); ?> 件</td>
    </tr>
    <tr>
        <th>データ保存先</th>
        <td>database/cards.json</td>
    </tr>
</table>

<div class="button-area">
    <a href="index.php" class="button">メニューへ戻る</a>
</div>

</div>

<footer>CardCMS Version 1.3</footer>

</body>
</html>
