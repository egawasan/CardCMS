<?php

require_once __DIR__ . "/../includes/auth.php";

cardcms_require_admin();

date_default_timezone_set("Asia/Tokyo");

$id = $_GET['id'] ?? "";
$direction = $_GET['direction'] ?? "";
$cardsFile = "../database/cards.json";

function redirectToList()
{
    header("Location: ../admin/card_list.php");
    exit;
}

function sortOrderValue($card)
{
    $sortOrder = $card['sort_order'] ?? "";

    if ($sortOrder !== "" && is_numeric($sortOrder)) {
        return (int)$sortOrder;
    }

    return PHP_INT_MAX;
}

if ($id === "" || !in_array($direction, ["up", "down"], true)) {
    redirectToList();
}

$cards = json_decode(file_get_contents($cardsFile), true);

if (!is_array($cards)) {
    redirectToList();
}

usort($cards, function ($a, $b) {
    $orderCompare = sortOrderValue($a) <=> sortOrderValue($b);

    if ($orderCompare !== 0) {
        return $orderCompare;
    }

    return ((int)($a['id'] ?? 0)) <=> ((int)($b['id'] ?? 0));
});

$currentIndex = null;

foreach ($cards as $index => $card) {
    if ((string)($card['id'] ?? "") === (string)$id) {
        $currentIndex = $index;
        break;
    }
}

if ($currentIndex === null) {
    redirectToList();
}

$targetIndex = $direction === "up"
    ? $currentIndex - 1
    : $currentIndex + 1;

if (!isset($cards[$targetIndex])) {
    redirectToList();
}

$movedCardId = (string)($cards[$currentIndex]['id'] ?? "");
$targetCardId = (string)($cards[$targetIndex]['id'] ?? "");

$temporaryCard = $cards[$currentIndex];
$cards[$currentIndex] = $cards[$targetIndex];
$cards[$targetIndex] = $temporaryCard;

$now = date("Y-m-d H:i:s");

foreach ($cards as $index => &$card) {
    $card['sort_order'] = ($index + 1) * 10;

    $cardId = (string)($card['id'] ?? "");

    if ($cardId === $movedCardId || $cardId === $targetCardId) {
        $card['updated_at'] = $now;
    }
}

unset($card);

file_put_contents(
    $cardsFile,
    json_encode($cards, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

redirectToList();

?>
