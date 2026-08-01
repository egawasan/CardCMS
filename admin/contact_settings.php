<?php

require_once __DIR__ . "/../includes/auth.php";

cardcms_require_admin();

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function contactSettingsPath()
{
    return __DIR__ . "/../config/contact.php";
}

function loadContactSettings()
{
    $path = contactSettingsPath();

    if (!is_readable($path)) {
        return [];
    }

    $config = require $path;

    return is_array($config) ? $config : [];
}

function isPlaceholderValue($value)
{
    return strpos((string)$value, "ここに入れる") !== false;
}

function isEmptySetting($value)
{
    if (is_array($value)) {
        return count(array_filter($value, function ($item) {
            return trim((string)$item) !== "";
        })) === 0;
    }

    $value = trim((string)$value);

    return $value === "" || isPlaceholderValue($value);
}

function emailList($value)
{
    if (is_string($value)) {
        $value = preg_split("/[,;]/", $value);
    } elseif (!is_array($value)) {
        $value = [];
    }

    $emails = [];

    foreach ($value as $email) {
        $email = trim((string)$email);

        if ($email !== "") {
            $emails[] = $email;
        }
    }

    return $emails;
}

function maskEmail($email)
{
    $email = trim((string)$email);

    if ($email === "" || isPlaceholderValue($email)) {
        return "未設定";
    }

    if (strpos($email, "@") === false) {
        return "形式を確認";
    }

    [$local, $domain] = explode("@", $email, 2);
    $localStart = mb_substr($local, 0, 2);

    if ($localStart === "") {
        $localStart = "*";
    }

    return $localStart . "***@" . $domain;
}

function displayEmailSetting($value)
{
    if (isEmptySetting($value)) {
        return "未設定";
    }

    return implode(", ", array_map("maskEmail", emailList($value)));
}

function displayTextSetting($value)
{
    if (isEmptySetting($value)) {
        return "未設定";
    }

    return (string)$value;
}

function displayBodySetting($value)
{
    if (isEmptySetting($value)) {
        return "未設定";
    }

    return "本文あり（" . mb_strlen((string)$value) . "文字）";
}

function statusText($value, $required)
{
    if (isEmptySetting($value)) {
        return $required ? "未設定" : "任意";
    }

    return "設定済み";
}

function statusClass($value, $required)
{
    if (isEmptySetting($value)) {
        return $required ? "status-danger" : "status-muted";
    }

    return "status-ok";
}

function statusTextForRow($row, $config)
{
    if ($row["display"] === "boolean") {
        return array_key_exists($row["key"], $config) ? "設定済み" : "初期値";
    }

    return statusText($config[$row["key"]] ?? "", $row["required"]);
}

function statusClassForRow($row, $config)
{
    if ($row["display"] === "boolean") {
        return array_key_exists($row["key"], $config) ? "status-ok" : "status-muted";
    }

    return statusClass($config[$row["key"]] ?? "", $row["required"]);
}

$config = loadContactSettings();
$configExists = is_readable(contactSettingsPath());

$rows = [
    ["label" => "管理者宛メール", "key" => "to", "required" => true, "display" => "email"],
    ["label" => "BCC", "key" => "bcc", "required" => false, "display" => "email"],
    ["label" => "送信元メール", "key" => "from", "required" => true, "display" => "email"],
    ["label" => "送信者名", "key" => "from_name", "required" => true, "display" => "text"],
    ["label" => "返信先メール", "key" => "reply_to", "required" => false, "display" => "email"],
    ["label" => "管理者宛件名", "key" => "admin_subject", "required" => true, "display" => "text"],
    ["label" => "自動返信", "key" => "auto_reply_enabled", "required" => false, "display" => "boolean"],
    ["label" => "自動返信件名", "key" => "auto_reply_subject", "required" => false, "display" => "text"],
    ["label" => "自動返信本文", "key" => "auto_reply_body", "required" => false, "display" => "body"],
];

function displaySetting($row, $config)
{
    $value = $config[$row["key"]] ?? "";

    if ($row["display"] === "email") {
        return displayEmailSetting($value);
    }

    if ($row["display"] === "boolean") {
        return !array_key_exists($row["key"], $config) || (bool)$value ? "有効" : "無効";
    }

    if ($row["display"] === "body") {
        return displayBodySetting($value);
    }

    return displayTextSetting($value);
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>CardCMS - お問い合わせメール設定</title>

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
    max-width:900px;
    margin:30px auto;
    background:white;
    padding:30px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}

h2{
    margin-top:0;
}

.notice{
    background:#f7f9fb;
    border-left:5px solid #3498db;
    padding:14px 16px;
    line-height:1.7;
    margin:20px 0;
}

.warning{
    background:#fff5f5;
    border-left-color:#c0392b;
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
    vertical-align:top;
}

th{
    width:28%;
    color:#4f5f6f;
    background:#f7f9fb;
}

.status{
    display:inline-block;
    min-width:72px;
    padding:4px 8px;
    border-radius:6px;
    font-size:13px;
    text-align:center;
}

.status-ok{
    background:#e8f6ef;
    color:#1f7a4d;
    font-weight:bold;
}

.status-danger{
    background:#fdecea;
    color:#a32820;
    font-weight:bold;
}

.status-muted{
    background:#f1f3f5;
    color:#6c757d;
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

<h2>お問い合わせメール設定</h2>

<?php if ($configExists): ?>
    <div class="notice">
        現在の <code>config/contact.php</code> を読み込んでいます。
        メールアドレスは安全のため一部だけ表示しています。
        この画面では確認のみ行い、変更はまだできません。
    </div>
<?php else: ?>
    <div class="notice warning">
        <code>config/contact.php</code> がまだありません。
        お問い合わせフォームから送信するには、メール設定ファイルの作成が必要です。
    </div>
<?php endif; ?>

<table>
    <tr>
        <th>項目</th>
        <th>現在の状態</th>
        <th>設定内容</th>
    </tr>
    <?php foreach ($rows as $row): ?>
        <?php $value = $config[$row["key"]] ?? ""; ?>
        <tr>
            <th><?php echo h($row["label"]); ?></th>
            <td>
                <span class="status <?php echo h(statusClassForRow($row, $config)); ?>">
                    <?php echo h(statusTextForRow($row, $config)); ?>
                </span>
            </td>
            <td><?php echo h(displaySetting($row, $config)); ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<div class="button-area">
    <a href="index.php" class="button">メニューへ戻る</a>
</div>

</div>

<footer>CardCMS Version 1.7</footer>

</body>
</html>
