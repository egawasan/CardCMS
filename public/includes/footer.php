<nav class="fixed-menu" aria-label="固定メニュー">
    <ul>
        <li><a href="index.php#head-office-factory" class="factory">本社・工場</a></li>
        <li><a href="contact.php" class="mail">メール</a></li>
        <li><a href="tel:073-477-5000" class="tel">電話</a></li>
    </ul>
</nav>

<footer>CardCMS Version 1.4</footer>

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

function isUrl(text){
    return /^https?:\/\//.test(String(text).trim());
}

function appendValueContent(container, text, labelText){
    const valueText = String(text).trim();

    if (isUrl(valueText)) {
        const link = document.createElement('a');
        link.href = valueText;
        link.target = '_blank';
        link.rel = 'noopener';
        link.textContent = /Google|マップ/.test(labelText) ? 'Googleマップで見る' : valueText;
        container.appendChild(link);
        return;
    }

    container.textContent = valueText;
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
    appendValueContent(value, valueText, labelText);

    row.appendChild(label);
    row.appendChild(value);
    container.appendChild(row);
}

function appendInfoContinuation(container, line){
    const row = document.createElement('div');
    row.className = 'card-info-row card-info-continuation';

    const spacer = document.createElement('span');
    spacer.className = 'card-info-label';
    spacer.textContent = '';

    const value = document.createElement('span');
    value.className = 'card-info-value';
    appendValueContent(value, line.trim(), '');

    row.appendChild(spacer);
    row.appendChild(value);
    container.appendChild(row);
}

function createBodyElement(text){
    const container = document.createElement('div');
    container.className = 'card-text';
    let lastLineWasInfo = false;

    String(text).split(/\r?\n/).forEach(rawLine => {
        const line = rawLine.trim();

        if (line === '') {
            lastLineWasInfo = false;
            return;
        }

        if (/^[・\-*]\s*/.test(line)) {
            appendListItem(container, line);
            lastLineWasInfo = false;
            return;
        }

        if (/^[^:：]{1,14}[:：]/.test(line)) {
            appendInfoRow(container, line);
            lastLineWasInfo = true;
            return;
        }

        if (lastLineWasInfo) {
            appendInfoContinuation(container, line);
            return;
        }

        container.appendChild(createTextElement('p', 'card-paragraph', line));
        lastLineWasInfo = false;
    });

    return container;
}

function createContactLink(){
    const wrapper = document.createElement('p');
    wrapper.className = 'contact-form-link';

    const link = document.createElement('a');
    link.href = 'contact.php';
    link.textContent = 'お問い合わせフォームへ';

    wrapper.appendChild(link);
    return wrapper;
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

    if (card.slug !== 'top-intro') {
        body.appendChild(createTextElement('h3', 'card-title', card.title || '無題'));
    }

    if (card.body) {
        body.appendChild(createBodyElement(card.body));
    }

    if (card.slug === 'contact') {
        body.appendChild(createContactLink());
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

if (cardGrid) {
    const publishedCards = Array.isArray(window.cardData) ? window.cardData : [];

    if (cardCount) {
        cardCount.textContent = publishedCards.length + '件表示';
    }

    if (publishedCards.length === 0) {
        showEmptyMessage();
    } else {
        publishedCards.forEach(card => {
            cardGrid.appendChild(createCard(card));
        });
    }
}
</script>

</body>
</html>
