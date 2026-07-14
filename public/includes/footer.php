<nav class="fixed-menu" aria-label="固定メニュー">
    <ul>
        <li><a href="#honsha" class="factory">本社・工場</a></li>
        <li><a href="#mail" class="mail">メール</a></li>
        <li><a href="tel:073-477-5000" class="tel">電話</a></li>
    </ul>
</nav>

<footer>CardCMS Version 1.3</footer>

<script>
const cardGrid = document.getElementById('card-grid');
const cardCount = document.getElementById('card-count');

function createTextElement(tagName, className, text){
    const element = document.createElement(tagName);
    element.className = className;
    element.textContent = text;
    return element;
}

function createCard(card){
    const article = document.createElement('article');
    article.className = 'card';
    const layout = card.layout || 'Default';
    article.classList.add('layout-' + layout.toLowerCase());

    const imageBox = document.createElement('div');
    imageBox.className = 'card-image';

    if (card.image) {
        const image = document.createElement('img');
        image.src = '../uploads/' + encodeURIComponent(card.image);
        image.alt = card.title || 'カード画像';
        imageBox.appendChild(image);
    } else {
        imageBox.textContent = '画像なし';
    }

    const body = document.createElement('div');
    body.className = 'card-body';

    body.appendChild(createTextElement('div', 'category', card.category || '未分類'));
    body.appendChild(createTextElement('h3', 'card-title', card.title || '無題'));

    if (card.body) {
        body.appendChild(createTextElement('p', 'card-text', card.body));
    }

    if (card.image || layout !== 'Text') {
        article.appendChild(imageBox);
    }

    article.appendChild(body);

    return article;
}

function showEmptyMessage(){
    const empty = document.createElement('div');
    empty.className = 'empty';
    empty.textContent = '公開中のカードはありません。';
    cardGrid.appendChild(empty);
}

fetch('../database/cards.json')
    .then(response => response.json())
    .then(cards => {
        const publishedCards = Array.isArray(cards)
            ? cards
                .filter(card => card.published)
                .sort((a, b) => {
                    const orderA = Number(a.sort_order || 0);
                    const orderB = Number(b.sort_order || 0);

                    if (orderA !== orderB) {
                        return orderA - orderB;
                    }

                    return Number(b.id || 0) - Number(a.id || 0);
                })
            : [];

        cardCount.textContent = publishedCards.length + '件表示';

        if (publishedCards.length === 0) {
            showEmptyMessage();
            return;
        }

        publishedCards.forEach(card => {
            cardGrid.appendChild(createCard(card));
        });
    })
    .catch(() => {
        cardCount.textContent = '読み込みエラー';
        showEmptyMessage();
    });
</script>

</body>
</html>
