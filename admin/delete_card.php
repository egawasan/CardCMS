<?php

$id = $_GET['id'] ?? '';

$cards = json_decode(
    file_get_contents("../database/cards.json"),
    true
);

if (!is_array($cards)) {
    $cards = [];
}

$deleteCard = null;

foreach ($cards as $card) {

    if ((string)$card['id'] === (string)$id) {
        $deleteCard = $card;
        break;
    }

}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>CardCMS - カード削除</title>

<style>

body{
    margin:0;
    font-family:-apple-system,BlinkMacSystemFont,"Helvetica Neue",sans-serif;
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
    max-width:760px;
    margin:30px auto;
    background:white;
    padding:30px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}

h2{
    margin-top:0;
    color:#2c3e50;
}

.card-title{
    font-size:22px;
    font-weight:bold;
    margin:18px 0;
}

.warning{
    background:#fff4e5;
    border:1px solid #f5c26b;
    color:#7a4b00;
    padding:14px;
    border-radius:8px;
    margin:20px 0;
}

.button-area{
    margin-top:28px;
}

button,
.button{
    display:inline-block;
    border:none;
    border-radius:8px;
    padding:12px 22px;
    font-size:16px;
    text-decoration:none;
    cursor:pointer;
    margin-right:10px;
}

.delete{
    background:#c0392b;
    color:white;
}

.cancel{
    background:#ddd;
    color:#333;
}

</style>
</head>

<body>

<header>CardCMS 管理画面</header>

<div class="container">

<?php if ($deleteCard === null): ?>

<h2>カードが見つかりません</h2>

<p>指定されたカードは見つかりませんでした。</p>

<div class="button-area">
    <a href="card_list.php" class="button cancel">一覧へ戻る</a>
</div>

<?php else: ?>

<h2>カード削除</h2>

<p>次のカードを削除します。</p>

<div class="card-title">
    <?php echo htmlspecialchars($deleteCard['title']); ?>
</div>

<div class="warning">
    この操作は取り消せません。削除してよい場合だけ「削除する」を押してください。
</div>

<form action="../api/delete_card.php" method="post">
    <input
        type="hidden"
        name="id"
        value="<?php echo htmlspecialchars($deleteCard['id']); ?>">

    <div class="button-area">
        <button type="submit" class="delete">削除する</button>
        <a href="card_list.php" class="button cancel">キャンセル</a>
    </div>
</form>

<?php endif; ?>

</div>

</body>
</html>
