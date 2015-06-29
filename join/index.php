<?php
require('../dbconnect.php');

session_start();

if (!empty($_POST)) {
	// エラー項目の確認
	if ($_POST['name'] == '') {
		$error['name'] = 'blank';
	}
	if ($_POST['email'] == '') {
		$error['email'] = 'blank';
	} 
	if (strlen($_POST['password']) < 4) {
		$error['password'] = 'length';
	}
	if ($_POST['password'] == '') {
		$error['password'] = 'blank';
	}
	$fileName = $_FILES['image']['name'];
	if (!empty($fileName)) {
		$ext = substr($fileName, -3);
		if ($ext != 'jpg' && $ext != 'gif') {
			$error['image'] = 'type';
		}
	}

	// 重複アカウントのチェック
	if (empty($error)) {
		$sql = sprintf('SELECT COUNT(*) AS cnt FROM members WHERE email="%s"',
			mysqli_real_escape_string($db, $_POST['email'])
		);
		$record = mysqli_query($db, $sql) or die(mysqli_error($db));
		$table = mysqli_fetch_assoc($record);
		if ($table['cnt'] > 0) {
			$error['email'] = 'duplicate';
		}
	}

	if (empty($error)) {
	// 画像をアップロードする　
	$image = date('YmdHis') . $_FILES['image']['name'];
	move_uploaded_file($_FILES['image']['tmp_name'],'../member_picture/' . $image);

	$_SESSION['join'] = $_POST;
	$_SESSION['join']['image'] = $image;
		header('Location: check.php');
		exit();
	}
	$name = $_POST['name'];
	$email = $_POST['email'];
	$password = $_POST['password'];
} else {
	$name ='';
	$email = '';
	$password ='';
}

// 書き直し
if (isset($_REQUEST['action'])) {
	if ($_REQUEST['action'] == 'rewrite') {
		$_POST = $_SESSION['join'];
		$error['rewrite'] = true;
	} 		
}
?>


<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="../error.css">
	<title>Twitter風味な掲示板</title>
</head>

<body>
<h1>会員登録</h1>

<p>次のフォームに必要事項をご記入ください。</p>
<form action="" method="post" enctype="multipart/form-data">
	<dl>
		<dt>ニックネーム</dt>
			<dd>
				<input type="text" name="name" size="35" maxlength="255" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" />
				<?php if (isset($error['name'])): ?>
				<?php if ($error['name'] == 'blank'): ?>
				<p class="error">* ニックネームを入力してください</p>
				<?php endif; ?>
				<?php endif; ?>
			</dd>
		<dt>メールアドレス</dt>
			<dd>
				<input type="text" name="email" size="35" maxlength="255" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" />
				<?php if (isset($error['email'])): ?>
				<?php if ($error['email'] == 'blank'): ?>
				<p class="error">* メールアドレスを入力してください</p>
				<?php endif; ?>
				<?php if ($error['email'] == 'duplicate'): ?>
				<p class="error">* 指定されたメールアドレスはすでに登録されています</p>
				<?php endif; ?>
				<?php endif; ?>
			</dd>
		<dt>パスワード</dt>
			<dd>
				<input type="password" name="password" size="10" maxlength="20" value="<?php echo htmlspecialchars($password, ENT_QUOTES, 'UTF-8'); ?>" />
				<?php if (isset($error['password'])): ?>
				<?php if ($error['password'] == 'blank'): ?>
				<p class="error">* パスワードを入力してください</p>
				<?php endif; ?>
				<?php if ($error['password'] == 'length'): ?>
				<p class="error">* パスワードは4文字以上で入力してください</p>
				<?php endif; ?>
				<?php endif; ?>
			</dd>
		<dt>写真など</dt>
			<dd>
				<input type="file" name="image" size="35" />
				<?php if (isset($error['image'])): ?>
				<?php if ($error['image'] == 'type'):?>
				<p class="error"> * 写真などは「.gif」または「.jpg」の画像を指定してください</p>
				<?php endif;?>
				<?php endif;?>
				<?php if (!empty($error['image'])) :?>
				<p class="error"> * 恐れ入りますが、画像を改めて指定してください</p>
				<?php endif;?>
			</dd>
	</dl>
	<div><input type="submit" value="入力内容を確認する" /></div>
</form>
</body>
</html>
