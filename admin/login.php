<?php

require_once __DIR__ . "/../includes/auth.php";

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$next = cardcms_auth_safe_next($_GET["next"] ?? "index.php");
$error = "";

if (cardcms_auth_is_logged_in()) {
    header("Location: " . $next);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $next = cardcms_auth_safe_next($_POST["next"] ?? "index.php");
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    if (cardcms_auth_login($username, $password)) {
        header("Location: " . $next);
        exit;
    }

    $error = "ログインできませんでした。ユーザー名とパスワードを確認してください。";
}

$configured = cardcms_auth_is_configured();

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CardCMS - ログイン</title>

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
    font-size:26px;
    font-weight:bold;
}

.container{
    max-width:520px;
    margin:42px auto;
    background:white;
    padding:30px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}

h2{
    margin-top:0;
}

.message{
    background:#fff4e5;
    border:1px solid #f5c26b;
    border-radius:8px;
    color:#7a4b00;
    line-height:1.7;
    padding:14px;
}

.error{
    background:#fdecea;
    border:1px solid #e0a3a0;
    color:#8a1f16;
    border-radius:8px;
    margin-bottom:18px;
    padding:12px;
}

.row{
    margin-bottom:18px;
}

label{
    display:block;
    font-weight:bold;
    margin-bottom:8px;
}

input[type=text],
input[type=password]{
    width:100%;
    border:1px solid #c8d1dc;
    border-radius:6px;
    font-size:17px;
    padding:12px;
}

button{
    width:100%;
    background:#3498db;
    border:none;
    border-radius:8px;
    color:white;
    cursor:pointer;
    font-size:18px;
    padding:14px;
}

button:hover{
    background:#2980b9;
}

.note{
    color:#657486;
    font-size:14px;
    line-height:1.7;
    margin-top:18px;
}

.setup-link{
    display:inline-block;
    background:#3498db;
    border-radius:8px;
    color:white;
    margin-top:18px;
    padding:12px 18px;
    text-decoration:none;
}

.setup-link:hover{
    background:#2980b9;
}

code{
    background:#f3f6f9;
    border-radius:4px;
    padding:2px 5px;
}

</style>
</head>

<body>

<header>CardCMS 管理画面</header>

<div class="container">

<?php if (!$configured): ?>

    <h2>管理画面保護の設定が必要です</h2>

    <div class="message">
        <p>まだ管理画面用パスワードが設定されていないため、admin画面は開けません。</p>
        <p><code>config/auth.example.php</code> を参考に、<code>config/auth.php</code> を作成してください。</p>
    </div>

    <p class="note">
        パスワードそのものはGitHubやチャットには書かず、<code>config/auth.php</code> だけに保存します。
    </p>

<?php if (cardcms_auth_is_local_request()): ?>

    <a href="setup_auth.php" class="setup-link">初期設定を開く</a>

<?php endif; ?>

<?php else: ?>

    <h2>ログイン</h2>

<?php if ($error !== ""): ?>
    <div class="error"><?php echo h($error); ?></div>
<?php endif; ?>

    <form method="post">
        <input type="hidden" name="next" value="<?php echo h($next); ?>">

        <div class="row">
            <label>ユーザー名</label>
            <input type="text" name="username" autocomplete="username" required>
        </div>

        <div class="row">
            <label>パスワード</label>
            <input type="password" name="password" autocomplete="current-password" required>
        </div>

        <button type="submit">ログイン</button>
    </form>

<?php endif; ?>

</div>

</body>
</html>
