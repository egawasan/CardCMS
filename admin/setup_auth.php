<?php

require_once __DIR__ . "/../includes/auth.php";

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$error = "";
$success = false;
$isLocal = cardcms_auth_is_local_request();
$configured = cardcms_auth_is_configured();

if ($isLocal && !$configured && $_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim((string)($_POST["username"] ?? ""));
    $password = (string)($_POST["password"] ?? "");
    $passwordConfirm = (string)($_POST["password_confirm"] ?? "");

    if ($username === "") {
        $error = "ユーザー名を入力してください。";
    } elseif (!preg_match("/^[a-zA-Z0-9_.-]{3,40}$/", $username)) {
        $error = "ユーザー名は3文字以上40文字以下の半角英数字、記号（_ . -）で入力してください。";
    } elseif (strlen($password) < 8) {
        $error = "パスワードは8文字以上にしてください。";
    } elseif ($password !== $passwordConfirm) {
        $error = "確認用パスワードが一致しません。";
    } else {
        $configPath = cardcms_auth_config_path();
        $configDir = dirname($configPath);

        if (!is_writable($configDir)) {
            $error = "configフォルダに書き込みできません。";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $configContent = "<?php\n\nreturn [\n"
                . "    \"admin_username\" => " . var_export($username, true) . ",\n"
                . "    \"admin_password_hash\" => " . var_export($passwordHash, true) . ",\n"
                . "];\n";

            $temporaryPath = $configPath . ".tmp";
            $written = file_put_contents($temporaryPath, $configContent, LOCK_EX);

            if ($written === false || !rename($temporaryPath, $configPath)) {
                $error = "config/auth.php を作成できませんでした。";
            } else {
                chmod($configPath, 0600);
                $success = true;
                $configured = true;
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CardCMS - 初期設定</title>

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
    max-width:560px;
    margin:42px auto;
    background:white;
    padding:30px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}

h2{
    margin-top:0;
}

.message,
.error,
.success{
    border-radius:8px;
    line-height:1.7;
    margin-bottom:18px;
    padding:14px;
}

.message{
    background:#fff4e5;
    border:1px solid #f5c26b;
    color:#7a4b00;
}

.error{
    background:#fdecea;
    border:1px solid #e0a3a0;
    color:#8a1f16;
}

.success{
    background:#e9f7ef;
    border:1px solid #9fd3b0;
    color:#1f6f3f;
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

button,
.button{
    display:inline-block;
    background:#3498db;
    border:none;
    border-radius:8px;
    color:white;
    cursor:pointer;
    font-size:18px;
    padding:14px 18px;
    text-align:center;
    text-decoration:none;
    width:100%;
}

button:hover,
.button:hover{
    background:#2980b9;
}

.note{
    color:#657486;
    font-size:14px;
    line-height:1.7;
    margin-top:18px;
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

<?php if (!$isLocal): ?>

    <h2>初期設定はローカル専用です</h2>

    <div class="message">
        この画面は <code>localhost</code> から開いた場合だけ使用できます。
    </div>

<?php elseif ($success): ?>

    <h2>初期設定が完了しました</h2>

    <div class="success">
        <code>config/auth.php</code> を作成しました。
        パスワードはハッシュ化して保存されています。
    </div>

    <a href="login.php" class="button">ログイン画面へ</a>

<?php elseif ($configured): ?>

    <h2>設定済みです</h2>

    <div class="message">
        すでに <code>config/auth.php</code> が設定されています。
    </div>

    <a href="login.php" class="button">ログイン画面へ</a>

<?php else: ?>

    <h2>管理画面の初期設定</h2>

    <div class="message">
        ここで入力したパスワードは、そのまま保存せず、PHPでハッシュ化して <code>config/auth.php</code> に保存します。
    </div>

<?php if ($error !== ""): ?>
    <div class="error"><?php echo h($error); ?></div>
<?php endif; ?>

    <form method="post">
        <div class="row">
            <label>ユーザー名</label>
            <input type="text" name="username" autocomplete="username" required>
        </div>

        <div class="row">
            <label>パスワード</label>
            <input type="password" name="password" autocomplete="new-password" required>
        </div>

        <div class="row">
            <label>パスワード確認</label>
            <input type="password" name="password_confirm" autocomplete="new-password" required>
        </div>

        <button type="submit">設定する</button>
    </form>

    <p class="note">
        このパスワードはチャットやGitHubには書かず、江川さんだけが管理してください。
    </p>

<?php endif; ?>

</div>

</body>
</html>
