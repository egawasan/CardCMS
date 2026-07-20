<?php

require_once __DIR__ . "/../includes/auth.php";

cardcms_require_admin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../admin/index.php");
    exit;
}

$id = $_POST['id'] ?? '';

$cards = json_decode(
    file_get_contents("../database/cards.json"),
    true
);

if (!is_array($cards)) {
    $cards = [];
}

$newCards = [];
$deleted = false;

foreach ($cards as $card) {

    if ((string)$card['id'] === (string)$id) {
        $deleted = true;
        continue;
    }

    $newCards[] = $card;

}

if ($deleted) {
    file_put_contents(
        "../database/cards.json",
        json_encode($newCards, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
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

.container{
    max-width:720px;
    margin:40px auto;
    background:white;
    padding:30px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}

h1{
    margin-top:0;
    color:#2c3e50;
}

.button{
    display:inline-block;
    margin-top:20px;
    background:#3498db;
    color:white;
    text-decoration:none;
    padding:12px 20px;
    border-radius:8px;
}

</style>
</head>

<body>

<div class="container">

<?php if ($deleted): ?>

<h1>削除しました</h1>
<p>カードを削除しました。</p>

<?php else: ?>

<h1>削除できませんでした</h1>
<p>指定されたカードが見つかりませんでした。</p>

<?php endif; ?>

<a href="../admin/card_list.php" class="button">一覧へ戻る</a>

</div>

</body>
</html>
