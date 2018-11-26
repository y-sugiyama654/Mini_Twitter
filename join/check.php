<?php
session_start();
require('../dbconnect.php');

//$_SESSION['join']になにも含まれていない場合は，
//URL直打ちでcheck.phpが呼び出された可能性があるため，
//index.phpに移動させる．
if(!isset($_SESSION['join'])) {
    header('Location: index.php');
    exit();
}

if (!empty($_POST)) {
    //登録処理する
    $sql = sprintf('INSERT INTO members SET name="%s", email="%s", password="%s", picture="%s", created="%s"',
        mysqli_real_escape_string($db, $_SESSION['join']['name']),
        mysqli_real_escape_string($db, $_SESSION['join']['email']),
        //「sha1」ファンクションを使ってパスワードを暗号化
        mysqli_real_escape_string($db, sha1($_SESSION['join']['password'])),
        mysqli_real_escape_string($db, $_SESSION['join']['image']), date('Y-m-d H:i:s')
        );
        //上記でSQLを書いたら，「mysqli_query」ファンクションでDBに実行
        mysqli_query($db, $sql) or die(mysqli_connect_error($db));
        //DBへの登録が終わったら，セッションから消す
        unset($_SESSION['join']);

        header('Location: thanks.php');
        exit();
}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Twitter風 SNS</title>
	</head>
	<body>
		<form action="" method="post">
            <input type="hidden" name="action" value="submit" />
			<dl>
				<dt>ニックネーム</dt>
				<dd>
					<?php echo htmlspecialchars($_SESSION['join']['name'], ENT_QUOTES, 'UTF-8'); ?>
				</dd>
				<dt>メールアドレス</dt>
				<dd>
					<?php echo htmlspecialchars($_SESSION['join']['email'], ENT_QUOTES, 'UTF-8'); ?>
				</dd>
				<dt>パスワード</dt>
				<dd>
					【表示されません】
				</dd>
				<dt>写真など</dt>
				<dd>
					<img src="../member_picture/<?php echo $_SESSION['join']['image']; ?>" width="100" height="100" alt="" />
				</dd>
			</dl>
			<div>
				<a href="index.php?action=rewrite">&laquo;&nbsp;書き直す</a>
				<input type="submit" value="登録する" />
			</div>	
		</form>
	</body>
</html>