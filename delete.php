<?php
session_start();
require('dbconnect.php');

//ログインしているかどうかを検査
if (isset($_SESSION['id'])) {
    $id = $_REQUEST['id'];
    
    //投稿者IDとログインしているユーザーのIDを比べている
    $sql = sprintf('SELECT * FROM posts WHERE id=%d', 
        mysqli_real_escape_string($db, $id)
    );
    $record = mysqli_query($db, $sql) or die(mysqli_connect_error($db));
    $table = mysqli_fetch_assoc($record);
    if ($table['member_id'] == $_SESSION['id']) {
        //投稿を削除
        $sql = sprintf('DELETE FROM posts WHERE id=%d',
            mysqli_real_escape_string($db, $id)
        );
        mysqli_query($db, $sql) or die(mysqli_connect_error($db));
    }
}

//ログインしていない状態でこのファイルを呼び出すと，index.phpに遷移
header('Location: index.php');
exit();
?>