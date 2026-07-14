<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>有限会社市場工芸</title>
<style>
*{
    box-sizing:border-box;
}

:root{
    --page-max-width:1200px;
}

body{
    margin:0;
    font-family:-apple-system,BlinkMacSystemFont,"Helvetica Neue",sans-serif;
    background:#f4f6f8;
    color:#25313d;
    padding-bottom:74px;
}

.site-header{
    background:white;
    border-bottom:1px solid #d9e0e7;
    padding:18px;
}

.header-inner{
    width:92%;
    max-width:var(--page-max-width);
    margin:0 auto;
    display:flex;
    align-items:center;
    justify-content:center;
}

.site-logo{
    display:block;
    width:min(310px, 80vw);
    height:auto;
}

main{
    width:92%;
    max-width:var(--page-max-width);
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

.card.layout-hero{
    grid-column:1 / -1;
    border:none;
    box-shadow:none;
}

.card.layout-hero .card-image{
    aspect-ratio:16 / 9;
    max-height:620px;
}

.card.layout-hero .card-body{
    background:#243241;
    color:white;
    padding:28px 18px;
    text-align:center;
}

.card.layout-hero .category,
.card.layout-hero .card-title{
    display:none;
}

.card.layout-hero .card-text{
    max-width:760px;
    margin:0 auto;
    color:white;
    font-size:18px;
    line-height:1.9;
}

.card.layout-imageleft,
.card.layout-imageright{
    grid-column:1 / -1;
    display:grid;
    grid-template-columns:minmax(260px, 42%) 1fr;
    align-items:stretch;
}

.card.layout-imageright{
    grid-template-columns:1fr minmax(260px, 42%);
}

.card.layout-imageright .card-image{
    order:2;
}

.card.layout-imageleft .card-image,
.card.layout-imageright .card-image{
    min-height:260px;
    height:100%;
    aspect-ratio:auto;
    background:white;
}

.card.layout-imageleft .card-image img,
.card.layout-imageright .card-image img{
    object-fit:contain;
    padding:10px;
}

.card.layout-imageleft .card-body,
.card.layout-imageright .card-body{
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.card.layout-gallery .card-image{
    aspect-ratio:16 / 10;
}

.card.layout-gallery .card-body{
    padding:14px;
}

.card.layout-gallery .card-title{
    margin-bottom:0;
}

.card.layout-text{
    border-left:4px solid #2d9fd8;
}

.card.layout-text .card-body{
    padding:20px;
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

.info-section{
    width:92%;
    max-width:var(--page-max-width);
    margin:0 auto 28px;
    background:white;
    border:1px solid #d9e0e7;
    border-radius:8px;
    padding:22px;
}

.info-section h2{
    margin:0 0 14px;
    font-size:22px;
    color:#243241;
}

.info-table{
    width:100%;
    border-collapse:collapse;
}

.info-table th,
.info-table td{
    border-top:1px solid #e4e9ef;
    padding:12px;
    text-align:left;
    vertical-align:top;
}

.info-table th{
    width:120px;
    background:#f3f6f9;
    color:#4f5f6f;
}

.info-table a{
    color:#21618c;
}

.fixed-menu{
    position:fixed;
    left:0;
    right:0;
    bottom:0;
    z-index:100;
    background:white;
    box-shadow:0 -4px 14px rgba(31,45,61,.18);
}

.fixed-menu ul{
    list-style:none;
    margin:0 auto;
    padding:0;
    display:flex;
    width:100%;
    max-width:var(--page-max-width);
}

.fixed-menu li{
    flex:1;
}

.fixed-menu a{
    display:block;
    padding:14px 8px;
    color:white;
    text-align:center;
    text-decoration:none;
    font-weight:bold;
    font-size:15px;
}

.fixed-menu .factory{
    background:#1f2482;
}

.fixed-menu .mail{
    background:#2d9fd8;
}

.fixed-menu .tel{
    background:#db0000;
}

footer{
    width:92%;
    max-width:var(--page-max-width);
    margin:0 auto 32px;
    color:#8894a0;
    font-size:13px;
    text-align:center;
}

@media (max-width:640px){
    .card.layout-hero .card-text{
        font-size:16px;
    }

    .toolbar{
        align-items:flex-start;
        flex-direction:column;
    }

    .info-table th,
    .info-table td{
        display:block;
        width:100%;
    }

    .card.layout-imageleft,
    .card.layout-imageright{
        display:block;
    }

    .card.layout-imageright .card-image{
        order:0;
    }

    .card.layout-imageleft .card-image,
    .card.layout-imageright .card-image{
        min-height:0;
        aspect-ratio:4 / 3;
    }

    .fixed-menu a{
        font-size:14px;
        padding:13px 6px;
    }
}
</style>
</head>

<body>

<header class="site-header">
    <div class="header-inner">
        <img
            class="site-logo"
            src="https://www.ichiba-k.jp/2020/wp-content/uploads/2020/12/9b7d91cff75d2687ea6b30e403f540c9.png"
            alt="有限会社市場工芸">
    </div>
</header>
