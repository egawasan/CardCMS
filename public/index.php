<?php
$cardsFile = __DIR__ . "/../database/cards.json";
$cards = [];

if (is_readable($cardsFile)) {
    $cardsJson = file_get_contents($cardsFile);
    $decodedCards = json_decode($cardsJson, true);

    if (is_array($decodedCards)) {
        $cards = $decodedCards;
    }
}

$publishedCards = array_values(array_filter($cards, static function ($card) {
    if (!is_array($card) || !array_key_exists('published', $card)) {
        return false;
    }

    $published = $card['published'];

    return $published === true
        || $published === 1
        || $published === '1'
        || $published === 'true';
}));

usort($publishedCards, static function ($a, $b) {
    $orderA = isset($a['sort_order']) ? (int)$a['sort_order'] : 0;
    $orderB = isset($b['sort_order']) ? (int)$b['sort_order'] : 0;

    if ($orderA !== $orderB) {
        return $orderA <=> $orderB;
    }

    $idA = isset($a['id']) ? (int)$a['id'] : 0;
    $idB = isset($b['id']) ? (int)$b['id'] : 0;

    return $idB <=> $idA;
});

$publicCards = array_map(static function ($card) {
    $images = [];

    if (isset($card['images']) && is_array($card['images'])) {
        foreach ($card['images'] as $image) {
            if ($image !== null && $image !== '') {
                $images[] = (string)$image;
            }
        }
    }

    return [
        'layout' => isset($card['layout']) ? (string)$card['layout'] : 'Default',
        'slug' => isset($card['slug']) ? (string)$card['slug'] : '',
        'title' => isset($card['title']) ? (string)$card['title'] : '',
        'body' => isset($card['body']) ? (string)$card['body'] : '',
        'image' => isset($card['image']) ? (string)$card['image'] : '',
        'images' => $images,
    ];
}, $publishedCards);

$publishedCardsJson = json_encode(
    $publicCards,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
);

if ($publishedCardsJson === false) {
    $publishedCardsJson = '[]';
}
?>

<?php include "includes/header.php"; ?>

<main>
    <div class="card-grid" id="card-grid"></div>
</main>

<script>
window.cardData = <?php echo $publishedCardsJson; ?>;
</script>

<?php include "includes/footer.php"; ?>
