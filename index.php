<?php
session_start();
require('dbconnect.php');

if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
	// ログインしている
	$_SESSION['time'] = time();

	$sql = sprintf('SELECT * FROM members WHERE id=%d',
		mysqli_real_escape_string($db, $_SESSION['id'])
	);
	$record = mysqli_query($db, $sql) or die(mysqli_error($db));
	$member = mysqli_fetch_assoc($record);
	} else {
	// ログインしていない
	header('Location: login.php');
	exit();
}

// 投稿を記録する
if (!empty($_POST)) {
	if ($_POST['message'] != '') {
		$sql = sprintf('INSERT INTO posts SET member_id=%d, message="%s", reply_post_id=%d, created=NOW()',
			mysqli_real_escape_string($db, $member['id']),
			mysqli_real_escape_string($db, $_POST['message']),
			mysqli_real_escape_string($db, $_POST['reply_post_id'])
		);
	mysqli_query($db, $sql) or die(mysqli_error($db));
			header('Location: index.php');
	exit();
	}
	$message = $_POST['message'];
} else {
	$message = '';
}

if (isset($_REQUEST['page'])) {
	// 投稿を取得する
	$page = $_REQUEST['page'];
	if ($page == '') {
		$page = 1;
	}
	$page = max($page, 1);
	// 最終ページを取得する
	$sql = 'SELECT COUNT(*) AS cnt FROM posts';
	$recordSet = mysqli_query($db, $sql);
	$table = mysqli_fetch_assoc($recordSet);
	$maxPage = ceil($table['cnt'] / 5);
	$page = min($page, $maxPage);

	$start = ($page - 1) * 5;
	$start = max(0, $start);	
}

$sql = sprintf('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT %d, 5',
	$start
);

$posts = mysqli_query($db, $sql) or die(mysqli_error($db));

// 返信の場合　
if (isset($_REQUEST['res'])) {
	$sql = sprintf('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=%d ORDER BY p.created DESC',
		mysqli_real_escape_string($db, $_REQUEST['res'])
	);
	$record = mysqli_query($db, $sql) or die(mysqli_error($db));
	$table = mysqli_fetch_assoc($record);
	$message = '@' . $table['name'] . ' ' . $table['message'];
}

// htmlspecialcharsのショートカット
function h($value) {
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// 本文内のURLにリンクを設定します
function makeLink($value) {
	return mb_ereg_replace("(https?)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)", '<a href="\1\2">\1\2</a>' , $value);
}
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
		<div style="text-align: right"><a href="logout.php">ログアウト</a></div>
	<form action="" method="post">
		<dl>
			<dt><?php echo htmlspecialchars($member['name']); ?>さん、メッセージをどうぞ</dt>
			<dd>
			<textarea name="message" cols="50" rows="5"><?php echo h($message, ENT_QUOTES, 'UTF-8'); ?></textarea>
			<input type="hidden" name="reply_post_id" value="<?php echo h($_REQUEST['res'], ENT_QUOTES, 'UTF-8'); ?>">
			</dd>
		</dl>
		<p>
			<input type="submit" value="投稿する">
		</p>
		</form>

	<?php while($post = mysqli_fetch_assoc($posts)): ?>
		<img src="member_picture/<?php echo h($post['picture']); ?>" width="48" height="48" alt="<?php echo h($post['name']); ?>">
		<p><?php echo makeLink(h($post['message'])); ?><?php echo h($post['name']); ?><a href="index.php?res=<?php echo h($post['id']); ?>">[Re</a>]</p>
		<p><a href="view.php?id=<?php echo h($post['id']); ?>"><?php echo h($post['created']); ?></a>
			<?php if ($post['reply_post_id'] > 0): ?>
				<a href="view.php?id=<?php echo h($post['reply_post_id']); ?>">返信元のメッセージ</a>
			<?php endif; ?>
			<?php if ($_SESSION['id'] == $post['member_id']): ?>
				[<a href="delete.php?id=<?php echo h($post['id']); ?>" style="color: #F33;">削除</a>]
			<?php endif; ?>
		</p>
	<?php endwhile; ?>

	<ul>
		<?php if ($page > 1) { ?>
			<li><a href="index.php?page=<?php print($page - 1); ?>">前のページへ</a></li>
		<?php } else { ?>
			<li>前のページへ</li>
		<?php } ?>
		<?php if ($page < $maxPage) { ?>
			<li><a href="index.php?page=<?php print($page + 1); ?>">次のページへ </a></li>
		<?php } else { ?>
			<li>次のページへ</li>
		<?php } ?>
	</ul>
</body>
</html>
