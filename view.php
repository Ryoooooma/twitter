<?php
session_start();
require('dbconnect.php');

if (empty($_REQUEST['id'])) {
	header('Location: index.php');
	exit();
}

// 投稿をここで取得
$sql = sprintf('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=%d ORDER BY p.created DESC',
	mysqli_real_escape_string($db, $_REQUEST['id'])
);
$posts = mysqli_query($db, $sql) or die(mysqli_error($db));
?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="error.css">
	<title>Twitter風味な掲示板</title>
</head>

<body>
	<h1>ひとこと掲示板</h1>
	<p>&laquo;<a href="index.php">一覧にもどる</a></p>
<?php if ($post = mysqli_fetch_assoc($posts)) :?>

		<img src="member_picture/<?php echo htmlspecialchars($post['picture'], ENT_QUOTES, 'UTF-8'); ?>" width="48" height="48" alt="<?php echo htmlspecialchars($post['name'], ENT_QUOTES, 'UTF-8'); ?>" />
		<p><?php echo htmlspecialchars($post['message'], ENT_QUOTES,'UTF-8'); ?>
		<span class="name">
			（<?php echo htmlspecialchars($post['name'], ENT_QUOTES, 'UTF-8'); ?>）
		</span>
		</p>

	<p class="day">
		<?php echo htmlspecialchars($post['created'], ENT_QUOTES, 'UTF-8'); ?>
	</p>

<?php else: ?>
	<p>その投稿は削除されたか、URLが間違えています</p>
<?php endif ;?>
</body>
</html>