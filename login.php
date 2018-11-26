<?php 
require('dbconnect.php');

session_start();

//エラーを非表示化
error_reporting(0);

//Cookieにemailとpasswordを保存された状態で，アクセスした際に
//Cookie保存したと判断して，emailとpasswordを$_POSTに格納
if ($_COOKIE['email'] != '') {
    $_POST['email'] = $_COOKIE['email'];
    $_POST['password'] = $_COOKIE['password'];
    $_POST['save'] = 'on';
}

//エラーを非表示化
error_reporting(0);

//ログインボタンが押されて遷移されているかの確認
if (!empty($_POST)) {
    //['email']と['password']のどちらかが空ではない
    if ($_POST['email'] != '' && $_POST['password'] != '') {
        //DBからemailとpasswordのデータを検索
        $sql = sprintf('SELECT * FROM members WHERE email="%s" AND password="%s"',
            mysqli_real_escape_string($db, $_POST['email']),
            mysqli_real_escape_string($db, sha1($_POST['password']))
        );
        $record = mysqli_query($db, $sql) or die(mysqli_connect_error($db));
        if ($table = mysqli_fetch_assoc($record)) {
            //ログイン成功
            $_SESSION['id'] = $table['id'];
            $_SESSION['time'] = time();
            
            //ログイン情報をCookieに記録する
            if ($_POST['save'] == 'on') {
                setcookie('email', $_POST['email'], time()+60*60*24*14);
                setcookie('password', $_POST['password'], time()+60*60*24*14);
            }
            
            header('Location: index.php');
            exit();
        } else {
            $error['login'] = 'failed';
        }
    //['email']と['password']のどちらかが空の場合
    } else {
        $error['login'] = 'blank';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Twitter風 SNS</title>
</head>
<body>
    <div id="lead">
        <p>メールアドレスとパスワードを記入してログインしてください</p>
        <p>入会手続きがまだの方はこちらからどうぞ</p>
        <p>&raquo;<a href="join/">入会手続きをする</a></p>
    </div>
    <form action="" method="post">
        <dl>
            <dt>メールアドレス</dt>
            <dd>
                <input type="text" name="email" size="35" maxlength="255" value="<?php echo htmlspecialchars($_POST['email']); ?>" />
                <?php if ($error['login'] == 'blank'): ?>
                    <p class="error">*メールアドレスとパスワードをご記入ください</p>
                <?php endif; ?>
                <?php if ($error['login'] == 'failed'): ?>
                    <p class="error">*ログインに失敗しました．正しくご記入ください．</p>
                <?php endif; ?>
            </dd>
            
            <dt>パスワード</dt>
            <dd>
                <input type="password" name="password" size="35" maxlength="255" value="<?php echo htmlspecialchars($_POST['password']); ?>" />
            </dd>
            
            <dt>ログイン情報の記録</dt>
            <dd>
                <input type="checkbox" id="save" name="save" value="on"><label for="save">次回からは自動的にログインする</label>
            </dd>
        </dl>
        <div><input type="submit" value="ログインする" /></div>
    </form>
</body>
</html>