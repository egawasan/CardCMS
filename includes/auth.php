<?php

function cardcms_auth_start_session()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_name("CARDCMS_ADMIN");
        session_start();
    }
}

function cardcms_auth_config_path()
{
    return __DIR__ . "/../config/auth.php";
}

function cardcms_auth_is_local_request()
{
    $remoteAddress = (string)($_SERVER["REMOTE_ADDR"] ?? "");
    $httpHost = strtolower((string)($_SERVER["HTTP_HOST"] ?? ""));
    $httpHost = preg_replace("/:\d+$/", "", $httpHost);

    return in_array($remoteAddress, ["127.0.0.1", "::1"], true)
        || in_array($httpHost, ["localhost", "127.0.0.1", "[::1]"], true);
}

function cardcms_auth_config()
{
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $configPath = cardcms_auth_config_path();

    if (!is_readable($configPath)) {
        $config = [];
        return $config;
    }

    $loadedConfig = require $configPath;

    $config = is_array($loadedConfig) ? $loadedConfig : [];

    return $config;
}

function cardcms_auth_is_configured()
{
    $config = cardcms_auth_config();
    $username = trim((string)($config["admin_username"] ?? ""));
    $passwordHash = trim((string)($config["admin_password_hash"] ?? ""));

    return $username !== ""
        && $passwordHash !== ""
        && $passwordHash !== "password_hashで作成した値をここに入れる";
}

function cardcms_auth_is_logged_in()
{
    cardcms_auth_start_session();

    return !empty($_SESSION["cardcms_admin_logged_in"]);
}

function cardcms_auth_login($username, $password)
{
    if (!cardcms_auth_is_configured()) {
        return false;
    }

    $config = cardcms_auth_config();
    $validUsername = (string)$config["admin_username"];
    $validPasswordHash = (string)$config["admin_password_hash"];

    if (!hash_equals($validUsername, (string)$username)) {
        return false;
    }

    if (!password_verify((string)$password, $validPasswordHash)) {
        return false;
    }

    cardcms_auth_start_session();
    session_regenerate_id(true);

    $_SESSION["cardcms_admin_logged_in"] = true;
    $_SESSION["cardcms_admin_username"] = $validUsername;

    return true;
}

function cardcms_auth_logout()
{
    cardcms_auth_start_session();
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            "",
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}

function cardcms_auth_login_url()
{
    $scriptName = str_replace("\\", "/", (string)($_SERVER["SCRIPT_NAME"] ?? ""));
    $loginUrl = preg_replace("#/(admin|api)/[^/]*$#", "/admin/login.php", $scriptName);

    if (!is_string($loginUrl) || $loginUrl === $scriptName) {
        return "login.php";
    }

    return $loginUrl;
}

function cardcms_auth_safe_next($next)
{
    $next = trim((string)$next);

    if ($next === "") {
        return "index.php";
    }

    if (preg_match("#^https?://#i", $next) || strpos($next, "//") === 0) {
        return "index.php";
    }

    if (preg_match("/[\r\n]/", $next)) {
        return "index.php";
    }

    return $next;
}

function cardcms_require_admin()
{
    if (cardcms_auth_is_logged_in()) {
        return;
    }

    $next = cardcms_auth_safe_next($_SERVER["REQUEST_URI"] ?? "index.php");
    $loginUrl = cardcms_auth_login_url();

    header("Location: " . $loginUrl . "?next=" . rawurlencode($next));
    exit;
}
