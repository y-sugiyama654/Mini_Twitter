<?php
require('../dbconnect.php');

session_start();

//エラーを非表示化
error_reporting(0);

//「$_POST」が空でない，つまり「フォームが送信された」場合に内容チェックを開始
if (!empty($_POST)) {
	//ニックネームが空の場合，
	if ($_POST['name'] == '') {
		//blankを代入
		$error['name'] = 'blank';
	}
	//メールアドレスが空の場合，
	if ($_POST['email'] == '') {
		//blankを代入
		$error['email'] = 'blank';
	}
	//パスワードが4文字以下の場合，
	if (strlen($_POST['password']) < 4) {
		//lengthを代入
		$error['password'] = 'length';
	}
	//パスワードが空の場合，
	if ($_POST['password'] == '') {
		//blankを代入
		$error['password'] = 'blank';
	}

	$fileName = $_FILES['image']['name'];
	if (!empty($fileName)) {
		$ext = substr($fileName, -3);
		if ($ext != 'jpg' && $ext != 'gif') {
			$error['image'] = 'type';
		}
	}
    
    //重複アカウントのチェック
    if (empty($error)) {
        $sql = sprintf('SELECT COUNT(*) AS cnt FROM members WHERE email="%s"',
        mysqli_real_escape_string($db, $_POST['email'])
        );
        //DB上でクエリを実行し$recordに格納，接続に失敗した際は中断
        $record = mysqli_query($db, $sql) or die(mysqli_connect_error($db));
        //連想配列として結果の行を取得する
        $table = mysqli_fetch_assoc($record);
        if ($table['cnt'] > 0) {
            $error['email'] = 'duplicate';
        }
    }

	//もしエラーが空の場合（正常な場合）
	if (empty($error)){
		//画像をアップロードする
		$image = date('YmdHis').$_FILES['image']['name'];
		move_uploaded_file($_FILES['image']['tmp_name'], '../member_picture/'.$image);
		//$_POSTのデータをセッションに格納
		$_SESSION['join'] = $_POST;
		//$imageのデータをセッションに格納
		$_SESSION['join']['image'] = $image;
		//check.phpに遷移
		header('Location: check.php');
		exit();
	}
}

//書き直しの処理
//URLパラメータの「action」が「rewrite」という内容だった場合，
if ($_REQUEST['action'] == 'rewrite') {
	//$_SESSION['join']の内容を$_POSTに代入し，内容を書き戻す
	$_POST = $_SESSION['join'];
	//ファイルのアップロードカラムは値を再現できないので，
	//改めて画像を指定してもらうためのメッセージのために利用
	$error['rewrite'] = true;
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Twitter風 SNS</title>
</head>
<body>
	<p>次のフォームに必要事項をご記入ください</p>
	<form action="" method="post" enctype="multipart/form-data">
		<dl>
			<dt>ニックネーム<span class="required">必須</span></dt>
			<dd>
				<input type="text" name="name" size="35" maxlength="255" value="<?php echo htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8'); ?>" />
				<?php if ($error['name'] == 'blank'): ?>
					<p class="error">*ニックネームを入力してください</p>
				<?php endif; ?>
			</dd>
			

			<dt>メールアドレス<span class="required">必須</span></dt>
			<dd>
				<input type="text" name="email" size="35" maxlength="255" value="<?php echo htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8'); ?>" />
				<?php if ($error['email'] == 'blank'): ?>
					<p class="error">*メールアドレスを入力してください</p>
				<?php endif; ?>
                <?php if ($error['email'] == 'duplicate'): ?>
                <p class="error">*指定されたメールアドレスはすでに登録されています</p>
                <?php endif; ?>
			</dd>

			<dt>パスワード<span class="required">必須</span></dt>
			<dd>
				<input type="text" name="password" size="10" maxlength="20" value="<?php echo htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8'); ?>" />
				<?php if ($error['password'] == 'blank'): ?>
					<p class="error">*パスワードを入力してください</p>
				<?php endif; ?>
				<?php if ($error['email'] == 'length'): ?>
					<p class="error">*パスワードは4文字以上で入力してください</p>
				<?php endif; ?>
			</dd>

			<dt>写真など</dt>
			<dd><input type="file" name="image" size="35" /></dd>
			<?php if ($error['image'] == 'type'): ?>
				<p class="error"*写真などは「.gif」または「.jpg」の画像を指定してください></p>
			<?php endif; ?>
			<?php if (!empty($error)): ?>
				<p class="error">*恐れ入りますが，画像を改めて指定してください</p>
			<?php endif; ?>
		</dl>
		<div><input type="submit" value="入力内容を確認する" /></div>
	</form>
</body>
</html>