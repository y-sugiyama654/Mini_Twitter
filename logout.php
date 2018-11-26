<?php 
session_start();

//セッション情報を削除するために，空の配列を格納する
$_SESSION = array();

//セッションを切断するにはセッションクッキーも削除する。
//Note: セッション情報だけでなくセッションを破壊する。
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() -42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();

//クッキー情報を削除
setcookie('email', '', time()-3600);
setcookie('password', '', time()-3600);

//login.phpに遷移
header('Location: login.php');
exit();

?>