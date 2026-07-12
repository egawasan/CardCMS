<?php

// cards.json を読み込む
$cards = json_decode(
    file_get_contents("../database/cards.json"),
    true
);

// 読み込めなかったら空配列
if (!is_array($cards)) {
    $cards = [];
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>CardCMS 管理画面</title>

<style>

body{
    margin:0;
    font-family:-apple-system,BlinkMacSystemFont,sans-serif;
    background:#eef2f7;
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
    width:90%;
    max-width:1000px;
    margin:30px auto;
    background:white;
    padding:30px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#3498db;
    color:white;
    padding:12px;
}

td{
    border-bottom:1px solid #ddd;
    padding:12px;
}

.button{
    display:inline-block;
    margin-top:25px;
    background:#3498db;
    color:white;
    text-decoration:none;
    padding:12px 20px;
    border-radius:8px;
}

.button:hover{
    background:#2980b9;
}

.button.secondary{
    background:#27ae60;
    margin-left:10px;
}

.button.secondary:hover{
    background:#1f8f4f;
}

</style>

</head>

<body>

<header>

CardCMS 管理画面

</header>

<div class="container">

<h2>カード一覧</h2>

<table>

<tr>
<th>ID</th>
<th>画像</th>
<th>タイトル</th>
<th>カテゴリー</th>
<th>公開</th>
<th>操作</th>
</tr>

<?php foreach ($cards as $card): ?>

<tr>

    <td><?php echo $card['id']; ?></td>

    <td>

<?php if (!empty($card['image'])): ?>

<img
    src="../uploads/<?php echo htmlspecialchars($card['image']); ?>"
    width="80">

<?php else: ?>

画像なし

<?php endif; ?>

</td>

    <td><?php echo htmlspecialchars($card['title']); ?></td>

    <td><?php echo htmlspecialchars($card['category']); ?></td>

    <td>
        <?php
        echo $card['published'] ? "○" : "×";
        ?>
    </td>

    <td>
    <a href="card_edit.php?id=<?php echo $card['id']; ?>">✏ 編集</a>
    |
    <a href="delete_card.php?id=<?php echo $card['id']; ?>">🗑 削除</a>
</td>

</tr>

<?php endforeach; ?>

</table>

<a href="card_new.php" class="button">

＋ 新しいカードを作成

</a>

<a href="../public/index.html" class="button secondary" target="_blank">

公開ページを見る

</a>

</div>

</body>
</html>