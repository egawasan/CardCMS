<?php

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$uploadDir = "../uploads";
$imageFiles = glob($uploadDir . "/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);

if ($imageFiles === false) {
    $imageFiles = [];
}

$cards = json_decode(
    file_get_contents("../database/cards.json"),
    true
);

if (!is_array($cards)) {
    $cards = [];
}

$imageUsage = [];

foreach ($cards as $card) {

    if (empty($card['image'])) {
        continue;
    }

    $imageName = (string)$card['image'];

    if (!isset($imageUsage[$imageName])) {
        $imageUsage[$imageName] = [];
    }

    $imageUsage[$imageName][] = [
        "id" => $card['id'] ?? '',
        "title" => $card['title'] ?? '無題'
    ];

}

usort($imageFiles, function($a, $b) {
    return filemtime($b) <=> filemtime($a);
});

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>CardCMS - 画像管理</title>

<style>

*{
    box-sizing:border-box;
}

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
    width:92%;
    max-width:1100px;
    margin:30px auto;
    background:white;
    padding:30px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}

.toolbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:16px;
    margin-bottom:22px;
}

h2{
    margin:0;
}

.button{
    display:inline-block;
    background:#3498db;
    color:white;
    text-decoration:none;
    padding:10px 16px;
    border-radius:8px;
}

.button:hover{
    background:#2980b9;
}

.image-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:18px;
}

.image-card{
    border:1px solid #d9e0e7;
    border-radius:8px;
    overflow:hidden;
    background:#fff;
}

.preview{
    aspect-ratio:4 / 3;
    background:#e7ebef;
}

.preview img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}

.image-info{
    padding:14px;
}

.filename{
    font-weight:bold;
    word-break:break-all;
    margin-bottom:10px;
}

.meta{
    color:#5f6f7f;
    font-size:13px;
    line-height:1.7;
}

.usage{
    margin-top:12px;
    padding-top:12px;
    border-top:1px solid #e3e8ee;
    font-size:13px;
}

.usage-title{
    font-weight:bold;
    margin-bottom:6px;
}

.usage a{
    color:#21618c;
}

.unused{
    color:#9a6b00;
}

.empty{
    background:#f7f9fb;
    border:1px solid #d9e0e7;
    border-radius:8px;
    padding:28px;
    text-align:center;
    color:#697786;
}

footer{
    text-align:center;
    color:#888;
    margin:30px;
}

@media (max-width:640px){
    .toolbar{
        align-items:flex-start;
        flex-direction:column;
    }
}

</style>
</head>

<body>

<header>CardCMS 管理画面</header>

<div class="container">

    <div class="toolbar">
        <h2>画像管理</h2>
        <a href="index.php" class="button">メニューへ戻る</a>
    </div>

<?php if (count($imageFiles) === 0): ?>

    <div class="empty">
        アップロード済み画像はありません。
    </div>

<?php else: ?>

    <div class="image-grid">

<?php foreach ($imageFiles as $imagePath): ?>
<?php
    $filename = basename($imagePath);
    $filesize = filesize($imagePath);
    $updatedAt = date("Y-m-d H:i", filemtime($imagePath));
    $usedCards = $imageUsage[$filename] ?? [];
?>

        <div class="image-card">
            <div class="preview">
                <img src="../uploads/<?php echo h($filename); ?>" alt="<?php echo h($filename); ?>">
            </div>

            <div class="image-info">
                <div class="filename"><?php echo h($filename); ?></div>

                <div class="meta">
                    サイズ: <?php echo number_format($filesize / 1024, 1); ?> KB<br>
                    更新日: <?php echo h($updatedAt); ?>
                </div>

                <div class="usage">
                    <div class="usage-title">使用状況</div>

<?php if (count($usedCards) > 0): ?>
<?php foreach ($usedCards as $usedCard): ?>

                    <div>
                        <a href="card_edit.php?id=<?php echo h($usedCard['id']); ?>">
                            <?php echo h($usedCard['title']); ?>
                        </a>
                    </div>

<?php endforeach; ?>
<?php else: ?>

                    <div class="unused">この画像を使っているカードはありません。</div>

<?php endif; ?>

                </div>
            </div>
        </div>

<?php endforeach; ?>

    </div>

<?php endif; ?>

</div>

<footer>CardCMS Version 1.1</footer>

</body>
</html>
