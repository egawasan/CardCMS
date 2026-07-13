<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>CardCMS 管理画面</title>

	<style>
		body{
			margin:0;
			font-family:-apple-system,BlinkMacSystemFont,"Helvetica Neue",Arial,sans-serif;
			background:#f5f5f5;
		}

		header{
			background:#2c3e50;
			color:#fff;
			padding:20px;
			text-align:center;
			font-size:24px;
		}

		.container{
			max-width:600px;
			margin:40px auto;
			padding:20px;
		}

		.menu{
			display:flex;
			flex-direction:column;
			gap:15px;
		}

		.menu a,
		.menu .disabled{
			display:block;
			font-size:18px;
			padding:18px;
			border:none;
			border-radius:8px;
			background:#3498db;
			color:white;
			text-align:center;
			text-decoration:none;
			transition:.2s;
		}

		.menu a:hover{
			background:#2980b9;
		}

		.menu .public{
			background:#27ae60;
		}

		.menu .public:hover{
			background:#1f8f4f;
		}

		.menu .disabled{
			background:#95a5a6;
			cursor:not-allowed;
		}

		footer{
			text-align:center;
			color:#777;
			margin-top:50px;
			font-size:13px;
		}
	</style>
</head>

<body>

<header>
	CardCMS 管理画面
</header>

<div class="container">

	<div class="menu">

		<a href="card_new.php">📄 新しいカード</a>

		<a href="card_list.php">📋 カード一覧</a>

		<a href="../public/index.html" class="public" target="_blank">🌐 公開ページを見る</a>

		<a href="image_manager.php">🖼 画像管理</a>

		<a href="settings.php">⚙️ システム情報</a>

	</div>

</div>

<footer>
	CardCMS Version 1.0
</footer>

</body>
</html>
