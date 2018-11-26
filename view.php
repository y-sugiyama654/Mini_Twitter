<?php
session_start();
require('dbconnect.php');

if (empty($_REQUEST['id'])) {
    header('Location: index.php');
    exit();
}

//投稿を取得する
$sql = sprintf('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=%d ORDER BY p.created DESC',
        mysqli_real_escape_string($db, $_REQUEST['id'])    
        );
$posts = mysqli_query($db, $sql) or die(mysqli_connect_error($db));
?>

<!DOCTYPE html>
<html>

<head>
    <title>Twitter風 SNS</title>
</head>

<body>
    <div id="wrap">
        <div id="head">
            <h1>ひとこと掲示板</h1>
        </div>
        
        <div id="content">
            <p>&laquo;<a href="index.php">一覧に戻る</a></p>
            <!--「mysqli_fetch_assoc」が正しく戻り値を返してくれたら，それを画面に表示 -->
            <?php if ($post = mysqli_fetch_assoc($posts)): ?>
                <div class="msg">
                    <img src="member_picture/<?php echo htmlspecialchars($post['picture'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($post['name'], ENT_QUOTES, 'UTS-8'); ?>" width="80" height="80" />
                    <p><?php echo htmlspecialchars($post['message'], ENT_QUOTES, 'UTF-8'); ?><span class="name">(<?php echo htmlspecialchars($post['name'], ENT_QUOTES, 'UTF-8'); ?>)</span></p>
                    <p class="day"><?php echo htmlspecialchars($post['created'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            <!-- もし結果がなければ，エラーメッセージを出力 -->
            <?php else: ?>
                <p>その投稿は削除されたか，URLが間違えています</p>
            <?php endif; ?>
        </div>
        
        <div id="foot">
            <p>(C) H2O Space. MYNAVI</p>
        </div>

    </div>
</body>

</html>
