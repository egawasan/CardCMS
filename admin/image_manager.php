<?php

require_once __DIR__ . "/../includes/auth.php";

cardcms_require_admin();

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function uploadErrorText($errorCode)
{
    $messages = [
        UPLOAD_ERR_INI_SIZE => "ファイルサイズがサーバーの上限を超えています。",
        UPLOAD_ERR_FORM_SIZE => "ファイルサイズがフォームの上限を超えています。",
        UPLOAD_ERR_PARTIAL => "アップロードが途中で止まりました。",
        UPLOAD_ERR_NO_FILE => "ファイルが選択されていません。",
        UPLOAD_ERR_NO_TMP_DIR => "一時保存フォルダが見つかりません。",
        UPLOAD_ERR_CANT_WRITE => "サーバーへ書き込めませんでした。",
        UPLOAD_ERR_EXTENSION => "サーバー側でアップロードが止められました。",
    ];

    return $messages[$errorCode] ?? "アップロードできませんでした。";
}

function safeImageFilename($originalName, $extension)
{
    $baseName = pathinfo((string)$originalName, PATHINFO_FILENAME);
    $baseName = preg_replace("/[^A-Za-z0-9_-]+/", "-", $baseName);
    $baseName = trim((string)$baseName, "-_");

    if ($baseName === "") {
        $baseName = "image";
    }

    return date("Ymd_His") . "_" . bin2hex(random_bytes(4)) . "_" . $baseName . "." . $extension;
}

function uploadOneImage($file, $uploadDir)
{
    $allowedExtensions = ["jpg", "jpeg", "png", "gif", "webp"];
    $allowedMimeTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
    $maxBytes = 10 * 1024 * 1024;

    if (($file["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ["skipped" => true];
    }

    if (($file["error"] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ["error" => uploadErrorText((int)$file["error"])];
    }

    if (($file["size"] ?? 0) > $maxBytes) {
        return ["error" => "10MBを超える画像はアップロードできません。"];
    }

    $extension = strtolower(pathinfo((string)($file["name"] ?? ""), PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions, true)) {
        return ["error" => "jpg、png、gif、webp の画像だけアップロードできます。"];
    }

    $tmpName = (string)($file["tmp_name"] ?? "");
    $imageInfo = @getimagesize($tmpName);

    if ($imageInfo === false || !in_array((string)($imageInfo["mime"] ?? ""), $allowedMimeTypes, true)) {
        return ["error" => "画像ファイルとして確認できませんでした。"];
    }

    if (!is_uploaded_file($tmpName)) {
        return ["error" => "アップロードされたファイルとして確認できませんでした。"];
    }

    $filename = safeImageFilename($file["name"] ?? "image", $extension);
    $targetPath = rtrim($uploadDir, "/") . "/" . $filename;

    if (!move_uploaded_file($tmpName, $targetPath)) {
        return ["error" => "uploadsフォルダへ保存できませんでした。"];
    }

    return ["filename" => $filename];
}

function normalizeUploadedFiles($files)
{
    if (!isset($files["name"])) {
        return [];
    }

    if (!is_array($files["name"])) {
        return [$files];
    }

    $normalized = [];
    $count = count($files["name"]);

    for ($i = 0; $i < $count; $i++) {
        $normalized[] = [
            "name" => $files["name"][$i] ?? "",
            "type" => $files["type"][$i] ?? "",
            "tmp_name" => $files["tmp_name"][$i] ?? "",
            "error" => $files["error"][$i] ?? UPLOAD_ERR_NO_FILE,
            "size" => $files["size"][$i] ?? 0,
        ];
    }

    return $normalized;
}

function localImageFilename($filename)
{
    $filename = basename((string)$filename);

    if ($filename === "") {
        return "";
    }

    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($extension, ["jpg", "jpeg", "png", "gif", "webp"], true)) {
        return "";
    }

    return $filename;
}

function addImageUsage(&$imageUsage, $imageName, $card) {
    if (empty($imageName)) {
        return;
    }

    $imageName = (string)$imageName;

    if (preg_match("/^https?:\/\//", $imageName)) {
        $imageName = basename(parse_url($imageName, PHP_URL_PATH) ?? $imageName);
    }

    if (!isset($imageUsage[$imageName])) {
        $imageUsage[$imageName] = [];
    }

    $imageUsage[$imageName][] = [
        "id" => $card['id'] ?? '',
        "title" => $card['title'] ?? '無題'
    ];
}

$uploadDir = __DIR__ . "/../uploads";
$uploadMessages = [];
$uploadErrors = [];

$cards = json_decode(
    file_get_contents(__DIR__ . "/../database/cards.json"),
    true
);

if (!is_array($cards)) {
    $cards = [];
}

$imageUsage = [];

foreach ($cards as $card) {
    addImageUsage($imageUsage, $card['image'] ?? '', $card);

    if (!empty($card['images']) && is_array($card['images'])) {
        foreach ($card['images'] as $imageName) {
            addImageUsage($imageUsage, $imageName, $card);
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "upload";

    if ($action === "delete_image") {
        $filename = localImageFilename($_POST["filename"] ?? "");
        $targetPath = $uploadDir . "/" . $filename;

        if ($filename === "" || !is_file($targetPath)) {
            $uploadErrors[] = "削除する画像を確認できませんでした。";
        } elseif (!empty($imageUsage[$filename])) {
            $uploadErrors[] = "この画像はカードで使用中のため削除できません。先にカードから画像を外してください。";
        } elseif (!unlink($targetPath)) {
            $uploadErrors[] = "画像を削除できませんでした。";
        } else {
            $uploadMessages[] = "削除しました: " . $filename;
        }
    } elseif (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        $uploadErrors[] = "uploadsフォルダを作成できませんでした。";
    } else {
        foreach (normalizeUploadedFiles($_FILES["images"] ?? []) as $file) {
            $result = uploadOneImage($file, $uploadDir);

            if (!empty($result["skipped"])) {
                continue;
            }

            if (!empty($result["error"])) {
                $uploadErrors[] = (string)$result["error"];
                continue;
            }

            $uploadMessages[] = "アップロードしました: " . (string)$result["filename"];
        }

        if (empty($uploadMessages) && empty($uploadErrors)) {
            $uploadErrors[] = "画像ファイルを選択してください。";
        }
    }
}

$imageFiles = glob($uploadDir . "/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);

if ($imageFiles === false) {
    $imageFiles = [];
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

.upload-panel{
    background:#f7f9fb;
    border:1px solid #d9e0e7;
    border-radius:8px;
    margin-bottom:24px;
    padding:18px;
}

.drop-zone{
    align-items:center;
    background:white;
    border:2px dashed #9fb3c8;
    border-radius:8px;
    color:#506070;
    cursor:pointer;
    display:flex;
    flex-direction:column;
    gap:8px;
    justify-content:center;
    min-height:150px;
    padding:24px;
    text-align:center;
}

.drop-zone.is-dragover{
    background:#edf7ff;
    border-color:#3498db;
}

.drop-title{
    font-size:18px;
    font-weight:bold;
}

.drop-sub{
    color:#6d7d8d;
    font-size:14px;
}

.file-input{
    display:none;
}

.selected-files{
    color:#506070;
    font-size:14px;
    line-height:1.7;
    margin-top:12px;
    white-space:pre-line;
}

.upload-button{
    background:#27ae60;
    border:none;
    border-radius:8px;
    color:white;
    cursor:pointer;
    font-size:16px;
    margin-top:14px;
    padding:12px 18px;
}

.upload-button:hover{
    background:#1f8f4f;
}

.messages{
    margin-bottom:18px;
}

.message,
.error{
    border-radius:8px;
    line-height:1.6;
    margin-bottom:8px;
    padding:10px 12px;
}

.message{
    background:#eaf7ef;
    border:1px solid #a8d9b9;
    color:#1f7a4d;
}

.error{
    background:#fdecea;
    border:1px solid #e0a3a0;
    color:#8a1f16;
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

.delete-form{
    margin-top:12px;
}

.delete-button{
    background:#c0392b;
    border:none;
    border-radius:6px;
    color:white;
    cursor:pointer;
    padding:9px 12px;
}

.delete-button:hover{
    background:#992d22;
}

.delete-disabled{
    color:#777;
    font-size:13px;
    margin-top:12px;
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

<?php if (!empty($uploadMessages) || !empty($uploadErrors)): ?>

    <div class="messages">
<?php foreach ($uploadMessages as $message): ?>
        <div class="message"><?php echo h($message); ?></div>
<?php endforeach; ?>

<?php foreach ($uploadErrors as $error): ?>
        <div class="error"><?php echo h($error); ?></div>
<?php endforeach; ?>
    </div>

<?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="upload-panel" id="image-upload-form">
        <input type="hidden" name="action" value="upload">
        <div class="drop-zone" id="image-drop-zone" tabindex="0">
            <div class="drop-title">画像をここへドラッグ＆ドロップ</div>
            <div class="drop-sub">またはクリックして画像を選択</div>
            <div class="drop-sub">jpg / png / gif / webp、10MBまで</div>
            <input
                type="file"
                name="images[]"
                id="image-upload-input"
                class="file-input"
                accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp"
                multiple>
        </div>

        <div class="selected-files" id="selected-files">選択中の画像はありません。</div>
        <button type="submit" class="upload-button">アップロード</button>
    </form>

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

<?php if (count($usedCards) === 0): ?>

                <form
                    method="post"
                    class="delete-form"
                    onsubmit="return confirm('この画像を削除しますか？');">
                    <input type="hidden" name="action" value="delete_image">
                    <input type="hidden" name="filename" value="<?php echo h($filename); ?>">
                    <button type="submit" class="delete-button">削除</button>
                </form>

<?php else: ?>

                <div class="delete-disabled">使用中のため削除できません。</div>

<?php endif; ?>

            </div>
        </div>

<?php endforeach; ?>

    </div>

<?php endif; ?>

</div>

<footer>CardCMS Version 1.4</footer>

<script>
const dropZone = document.getElementById('image-drop-zone');
const fileInput = document.getElementById('image-upload-input');
const selectedFiles = document.getElementById('selected-files');

function updateSelectedFiles(files){
    if (!files || files.length === 0) {
        selectedFiles.textContent = '選択中の画像はありません。';
        return;
    }

    selectedFiles.textContent = Array.from(files)
        .map(file => file.name)
        .join('\n');
}

dropZone.addEventListener('click', () => {
    fileInput.click();
});

dropZone.addEventListener('keydown', event => {
    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        fileInput.click();
    }
});

fileInput.addEventListener('change', () => {
    updateSelectedFiles(fileInput.files);
});

['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, event => {
        event.preventDefault();
        dropZone.classList.add('is-dragover');
    });
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, event => {
        event.preventDefault();
        dropZone.classList.remove('is-dragover');
    });
});

dropZone.addEventListener('drop', event => {
    fileInput.files = event.dataTransfer.files;
    updateSelectedFiles(fileInput.files);
});
</script>

</body>
</html>
