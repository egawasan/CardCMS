<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CardCMS 公開ページ</title>
<style>
*{
    box-sizing:border-box;
}

body{
    margin:0;
    font-family:-apple-system,BlinkMacSystemFont,"Helvetica Neue",sans-serif;
    background:#f4f6f8;
    color:#25313d;
}

header{
    background:#243241;
    color:white;
    padding:24px 18px;
}

.header-inner{
    width:92%;
    max-width:1080px;
    margin:0 auto;
}

.site-title{
    margin:0;
    font-size:28px;
    font-weight:bold;
}

.site-subtitle{
    margin:8px 0 0;
    color:#d8e1ea;
    font-size:15px;
}

main{
    width:92%;
    max-width:1080px;
    margin:28px auto 48px;
}

.toolbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:16px;
    margin-bottom:18px;
}

.toolbar h2{
    margin:0;
    font-size:22px;
}

.count{
    color:#6b7785;
    font-size:14px;
}

.card-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:18px;
}

.card{
    background:white;
    border:1px solid #d9e0e7;
    border-radius:8px;
    overflow:hidden;
    box-shadow:0 4px 12px rgba(31,45,61,.08);
}

.card-image{
    width:100%;
    aspect-ratio:4 / 3;
    background:#e7ebef;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#7b8794;
    font-size:14px;
}

.card-image img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}

.card-body{
    padding:16px;
}

.category{
    display:inline-block;
    padding:4px 8px;
    border-radius:4px;
    background:#eaf3fb;
    color:#21618c;
    font-size:12px;
    font-weight:bold;
    margin-bottom:10px;
}

.card-title{
    margin:0 0 10px;
    font-size:18px;
    line-height:1.4;
}

.card-text{
    margin:0;
    color:#4f5f6f;
    font-size:14px;
    line-height:1.7;
    white-space:pre-wrap;
}

.empty{
    background:white;
    border:1px solid #d9e0e7;
    border-radius:8px;
    padding:28px;
    text-align:center;
    color:#697786;
}

footer{
    width:92%;
    max-width:1080px;
    margin:0 auto 32px;
    color:#8894a0;
    font-size:13px;
    text-align:center;
}

@media (max-width:640px){
    .site-title{
        font-size:24px;
    }

    .toolbar{
        align-items:flex-start;
        flex-direction:column;
    }
}
</style>
</head>

<body>

<header>
    <div class="header-inner">
        <h1 class="site-title">CardCMS</h1>
        <p class="site-subtitle">公開中のカードを表示しています。</p>
    </div>
</header>
