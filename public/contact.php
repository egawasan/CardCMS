<?php

session_start();
date_default_timezone_set("Asia/Tokyo");

if (function_exists("mb_language")) {
    mb_language("Japanese");
}

if (function_exists("mb_internal_encoding")) {
    mb_internal_encoding("UTF-8");
}

$fields = [
    "name" => "お名前",
    "phone" => "電話番号",
    "address" => "住所",
    "email" => "メールアドレス",
    "message" => "お問い合わせ内容",
];

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function contactToken()
{
    if (empty($_SESSION["contact_token"])) {
        $_SESSION["contact_token"] = bin2hex(random_bytes(16));
    }

    return $_SESSION["contact_token"];
}

function submittedValues()
{
    return [
        "name" => trim((string)($_POST["name"] ?? "")),
        "phone" => trim((string)($_POST["phone"] ?? "")),
        "address" => trim((string)($_POST["address"] ?? "")),
        "email" => trim((string)($_POST["email"] ?? "")),
        "message" => trim((string)($_POST["message"] ?? "")),
        "website" => trim((string)($_POST["website"] ?? "")),
    ];
}

function validateContact($values)
{
    $errors = [];

    if ($values["name"] === "") {
        $errors["name"] = "お名前を入力してください。";
    } elseif (mb_strlen($values["name"]) > 80) {
        $errors["name"] = "お名前は80文字以内で入力してください。";
    }

    if ($values["phone"] === "") {
        $errors["phone"] = "電話番号を入力してください。";
    } elseif (!preg_match("/^[0-9０-９\\-ー−+()（）\\s]+$/u", $values["phone"])) {
        $errors["phone"] = "電話番号は数字とハイフンで入力してください。";
    }

    if ($values["address"] === "") {
        $errors["address"] = "住所を入力してください。";
    } elseif (mb_strlen($values["address"]) > 200) {
        $errors["address"] = "住所は200文字以内で入力してください。";
    }

    if ($values["email"] === "") {
        $errors["email"] = "メールアドレスを入力してください。";
    } elseif (!filter_var($values["email"], FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "メールアドレスの形式を確認してください。";
    }

    if ($values["message"] === "") {
        $errors["message"] = "お問い合わせ内容を入力してください。";
    } elseif (mb_strlen($values["message"]) > 3000) {
        $errors["message"] = "お問い合わせ内容は3000文字以内で入力してください。";
    }

    return $errors;
}

function isTokenValid()
{
    $token = (string)($_POST["contact_token"] ?? "");

    return isset($_SESSION["contact_token"]) && hash_equals($_SESSION["contact_token"], $token);
}

function contactConfig()
{
    $path = __DIR__ . "/../config/contact.php";

    if (!is_readable($path)) {
        return [];
    }

    $config = require $path;

    return is_array($config) ? $config : [];
}

function mailHeaderAddress($email)
{
    return str_replace(["\r", "\n"], "", (string)$email);
}

function mailHeaderAddressList($emails)
{
    if (is_string($emails)) {
        $emails = preg_split("/[,;]/", $emails);
    } elseif (!is_array($emails)) {
        $emails = [];
    }

    $addresses = [];

    foreach ($emails as $email) {
        $email = trim(mailHeaderAddress($email));

        if ($email !== "" && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $addresses[] = $email;
        }
    }

    return array_values(array_unique($addresses));
}

function encodeMailHeader($value)
{
    $value = mailHeaderAddress($value);

    if (function_exists("mb_encode_mimeheader")) {
        return mb_encode_mimeheader($value);
    }

    return $value;
}

function applyMailTemplate($text, $values)
{
    return str_replace(
        ["{name}", "{phone}", "{address}", "{email}", "{message}"],
        [$values["name"], $values["phone"], $values["address"], $values["email"], $values["message"]],
        (string)$text
    );
}

function buildAdminMailBody($values)
{
    return implode("\n", [
        "ホームページからお問い合わせがありました。",
        "",
        "お名前",
        $values["name"],
        "",
        "電話番号",
        $values["phone"],
        "",
        "住所",
        $values["address"],
        "",
        "メールアドレス",
        $values["email"],
        "",
        "お問い合わせ内容",
        $values["message"],
        "",
        "送信日時",
        date("Y-m-d H:i:s"),
    ]);
}

function buildAutoReplyMailBody($values, $config)
{
    $message = trim(applyMailTemplate((string)($config["auto_reply_body"] ?? ""), $values));

    if ($message === "") {
        $message = implode("\n", [
            $values["name"] . " 様",
            "",
            "お問い合わせありがとうございます。",
            "内容を確認のうえ、担当者よりご連絡いたします。",
        ]);
    }

    return implode("\n", [
        $message,
        "",
        "----- お問い合わせ内容 -----",
        "",
        "お名前",
        $values["name"],
        "",
        "電話番号",
        $values["phone"],
        "",
        "住所",
        $values["address"],
        "",
        "メールアドレス",
        $values["email"],
        "",
        "お問い合わせ内容",
        $values["message"],
    ]);
}

function sendPlainTextMail($to, $subject, $body, $headers)
{
    $subject = mailHeaderAddress($subject);
    $headerText = implode("\r\n", $headers);

    if (function_exists("mb_send_mail")) {
        return mb_send_mail($to, $subject, $body, $headerText);
    }

    return mail($to, encodeMailHeader($subject), $body, $headerText);
}

function sendContactMail($values, &$errorMessage)
{
    $config = contactConfig();
    $to = mailHeaderAddress($config["to"] ?? "");
    $from = mailHeaderAddress($config["from"] ?? "");
    $fromName = mailHeaderAddress($config["from_name"] ?? "有限会社市場工芸");
    $replyTo = mailHeaderAddress($config["reply_to"] ?? $from);
    $adminSubject = applyMailTemplate($config["admin_subject"] ?? $config["subject"] ?? "WEBよりお問い合わせ", $values);
    $autoReplySubject = applyMailTemplate($config["auto_reply_subject"] ?? "お問い合わせありがとうございました。", $values);
    $autoReplyEnabled = !array_key_exists("auto_reply_enabled", $config) || (bool)$config["auto_reply_enabled"];
    $bccAddresses = mailHeaderAddressList($config["bcc"] ?? []);

    if ($to === "" || $from === "") {
        $errorMessage = "送信先メールアドレスの設定がまだ完了していません。";
        return false;
    }

    $adminHeaders = [
        "From: " . encodeMailHeader($fromName) . " <" . $from . ">",
        "Reply-To: " . mailHeaderAddress($values["email"]),
    ];

    if (!empty($bccAddresses)) {
        $adminHeaders[] = "Bcc: " . implode(", ", $bccAddresses);
    }

    $adminHeaders[] = "Content-Type: text/plain; charset=UTF-8";
    $adminHeaders[] = "Content-Transfer-Encoding: 8bit";
    $adminHeaders[] = "X-Mailer: PHP/" . phpversion();

    if (!sendPlainTextMail($to, $adminSubject, buildAdminMailBody($values), $adminHeaders)) {
        return false;
    }

    if ($autoReplyEnabled) {
        $autoReplyHeaders = [
            "From: " . encodeMailHeader($fromName) . " <" . $from . ">",
            "Reply-To: " . ($replyTo !== "" ? $replyTo : $from),
            "Content-Type: text/plain; charset=UTF-8",
            "Content-Transfer-Encoding: 8bit",
            "X-Mailer: PHP/" . phpversion(),
        ];

        sendPlainTextMail($values["email"], $autoReplySubject, buildAutoReplyMailBody($values, $config), $autoReplyHeaders);
    }

    return true;
}

$action = (string)($_POST["contact_action"] ?? "input");
$values = submittedValues();
$errors = [];
$sendError = "";
$mode = "input";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isTokenValid()) {
        $errors["form"] = "フォームをもう一度開き直して送信してください。";
        $mode = "input";
    } elseif ($action === "confirm") {
        $errors = validateContact($values);
        $mode = empty($errors) ? "confirm" : "input";
    } elseif ($action === "send") {
        $errors = validateContact($values);

        if (empty($errors)) {
            if ($values["website"] !== "") {
                $mode = "complete";
            } elseif (sendContactMail($values, $sendError)) {
                unset($_SESSION["contact_token"]);
                contactToken();
                $mode = "complete";
            } else {
                $errors["form"] = $sendError !== "" ? $sendError : "送信できませんでした。時間をおいてもう一度お試しください。";
                $mode = "input";
            }
        } else {
            $mode = "input";
        }
    } else {
        $mode = "input";
    }
}

$token = contactToken();
?>

<?php include "includes/header.php"; ?>

<main class="contact-page">
    <section class="contact-panel">
        <h1>お問い合わせ</h1>

<?php if ($mode === "complete"): ?>

        <div class="contact-complete">
            <h2>送信しました</h2>
            <p>お問い合わせありがとうございます。内容を確認のうえ、担当者よりご連絡いたします。</p>
            <p><a href="index.php">トップページへ戻る</a></p>
        </div>

<?php elseif ($mode === "confirm"): ?>

        <p class="contact-lead">入力内容をご確認ください。</p>

        <form method="post" action="contact.php" class="contact-form">
            <input type="hidden" name="contact_token" value="<?php echo h($token); ?>">
            <input type="hidden" name="name" value="<?php echo h($values["name"]); ?>">
            <input type="hidden" name="phone" value="<?php echo h($values["phone"]); ?>">
            <input type="hidden" name="address" value="<?php echo h($values["address"]); ?>">
            <input type="hidden" name="email" value="<?php echo h($values["email"]); ?>">
            <input type="hidden" name="message" value="<?php echo h($values["message"]); ?>">
            <input type="hidden" name="website" value="<?php echo h($values["website"]); ?>">

            <dl class="confirm-list">
<?php foreach ($fields as $key => $label): ?>
                <div>
                    <dt><?php echo h($label); ?></dt>
                    <dd><?php echo nl2br(h($values[$key])); ?></dd>
                </div>
<?php endforeach; ?>
            </dl>

            <div class="form-actions">
                <button type="submit" name="contact_action" value="input" class="button-secondary">戻って修正</button>
                <button type="submit" name="contact_action" value="send" class="button-primary">送信する</button>
            </div>
        </form>

<?php else: ?>

        <p class="contact-lead">お気軽にお問い合わせください。<span>＊は必須項目です。</span></p>

<?php if (!empty($errors)): ?>
        <div class="error-box">
            <p>入力内容をご確認ください。</p>
        </div>
<?php endif; ?>

        <form method="post" action="contact.php" class="contact-form" novalidate>
            <input type="hidden" name="contact_token" value="<?php echo h($token); ?>">
            <input type="text" name="website" value="<?php echo h($values["website"]); ?>" class="website-field" tabindex="-1" autocomplete="off">

            <label>
                <span>お名前 <strong>＊</strong></span>
                <input type="text" name="name" value="<?php echo h($values["name"]); ?>" autocomplete="name" required>
                <?php if (!empty($errors["name"])): ?><em><?php echo h($errors["name"]); ?></em><?php endif; ?>
            </label>

            <label>
                <span>電話番号 <strong>＊</strong></span>
                <input type="tel" name="phone" value="<?php echo h($values["phone"]); ?>" autocomplete="tel" required>
                <?php if (!empty($errors["phone"])): ?><em><?php echo h($errors["phone"]); ?></em><?php endif; ?>
            </label>

            <label>
                <span>住所 <strong>＊</strong></span>
                <input type="text" name="address" value="<?php echo h($values["address"]); ?>" autocomplete="street-address" required>
                <?php if (!empty($errors["address"])): ?><em><?php echo h($errors["address"]); ?></em><?php endif; ?>
            </label>

            <label>
                <span>メールアドレス <strong>＊</strong></span>
                <input type="email" name="email" value="<?php echo h($values["email"]); ?>" autocomplete="email" required>
                <?php if (!empty($errors["email"])): ?><em><?php echo h($errors["email"]); ?></em><?php endif; ?>
            </label>

            <label>
                <span>お問い合わせ内容 <strong>＊</strong></span>
                <textarea name="message" rows="8" required><?php echo h($values["message"]); ?></textarea>
                <?php if (!empty($errors["message"])): ?><em><?php echo h($errors["message"]); ?></em><?php endif; ?>
            </label>

<?php if (!empty($errors["form"])): ?>
            <p class="form-error"><?php echo h($errors["form"]); ?></p>
<?php endif; ?>

            <div class="form-actions">
                <a href="index.php" class="button-secondary">戻る</a>
                <button type="submit" name="contact_action" value="confirm" class="button-primary">確認画面へ</button>
            </div>
        </form>

<?php endif; ?>

    </section>
</main>

<?php include "includes/footer.php"; ?>
