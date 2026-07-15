<nav class="fixed-menu" aria-label="固定メニュー">
    <ul>
        <li><a href="#head-office-factory" class="factory">本社・工場</a></li>
        <li><a href="#contact" class="mail">メール</a></li>
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

function appendListItem(container, line){
    let list = container.lastElementChild;

    if (!list || !list.classList.contains('card-list')) {
        list = document.createElement('ul');
        list.className = 'card-list';
        container.appendChild(list);
    }

    const item = document.createElement('li');
    item.textContent = line.replace(/^[・\-*]\s*/, '');
    list.appendChild(item);
}

function appendInfoRow(container, line){
    const parts = line.split(/[:：]/);
    const labelText = parts.shift().trim();
    const valueText = parts.join(':').trim();

    const row = document.createElement('div');
    row.className = 'card-info-row';

    const label = document.createElement('span');
    label.className = 'card-info-label';
    label.textContent = labelText;

    const value = document.createElement('span');
    value.className = 'card-info-value';
    value.textContent = valueText;

    row.appendChild(label);
    row.appendChild(value);
    container.appendChild(row);
}

function createBodyElement(text){
    const container = document.createElement('div');
    container.className = 'card-text';

    String(text).split(/\r?\n/).forEach(rawLine => {
        const line = rawLine.trim();

        if (line === '') {
            return;
        }

        if (/^[・\-*]\s*/.test(line)) {
            appendListItem(container, line);
            return;
        }

        if (/^[^:：]{1,14}[:：]/.test(line)) {
            appendInfoRow(container, line);
            return;
        }

        container.appendChild(createTextElement('p', 'card-paragraph', line));
    });

    return container;
}

function getImageSrc(imagePath){
    if (/^https?:\/\//.test(imagePath)) {
        return imagePath;
    }

    return '../uploads/' + encodeURIComponent(imagePath);
}

function getCardImages(card){
    const images = [];

    if (card.image) {
        images.push(card.image);
    }

    if (Array.isArray(card.images)) {
        card.images.forEach(imagePath => {
            if (imagePath) {
                images.push(imagePath);
            }
        });
    }

    return [...new Set(images)];
}

function createImageElement(imagePath, altText){
    const image = document.createElement('img');
    image.src = getImageSrc(imagePath);
    image.alt = altText || 'カード画像';
    return image;
}

function createSingleImageBox(imagePath, altText){
    const imageBox = document.createElement('div');
    imageBox.className = 'card-image';
    imageBox.appendChild(createImageElement(imagePath, altText));
    return imageBox;
}

function createImageList(card, imagePaths){
    if (imagePaths.length === 1 || (card.layout || '') === 'Hero') {
        return createSingleImageBox(imagePaths[0], card.title);
    }

    const imageList = document.createElement('div');
    imageList.className = 'card-image-list';

    imagePaths.forEach((imagePath, index) => {
        const imageBox = createSingleImageBox(
            imagePath,
            (card.title || 'カード画像') + ' ' + (index + 1)
        );

        imageList.appendChild(imageBox);
    });

    return imageList;
}

function createCard(card){
    const article = document.createElement('article');
    article.className = 'card';
    const layout = card.layout || 'Default';
    article.classList.add('layout-' + layout.toLowerCase());

    if (card.slug) {
        article.id = card.slug;
    }

    const body = document.createElement('div');
    body.className = 'card-body';

    body.appendChild(createTextElement('div', 'category', card.category || '未分類'));
    body.appendChild(createTextElement('h3', 'card-title', card.title || '無題'));

    if (card.body) {
        body.appendChild(createBodyElement(card.body));
    }

    const imagePaths = getCardImages(card);
    const showImage = imagePaths.length > 0;
    const showBody = layout !== 'Hero' || card.body;
    const imageBlock = showImage ? createImageList(card, imagePaths) : null;

    if (layout === 'Hero') {
        if (showImage) {
            article.appendChild(imageBlock);
        }

        if (showBody) {
            article.appendChild(body);
        }
    } else {
        if (showBody) {
            article.appendChild(body);
        }

        if (showImage) {
            article.appendChild(imageBlock);
        }
    }

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

        if (cardCount) {
            cardCount.textContent = publishedCards.length + '件表示';
        }

        if (publishedCards.length === 0) {
            showEmptyMessage();
            return;
        }

        publishedCards.forEach(card => {
            cardGrid.appendChild(createCard(card));
        });
    })
    .catch(() => {
        if (cardCount) {
            cardCount.textContent = '読み込みエラー';
        }
        showEmptyMessage();
    });
</script>

</body>
</html>
