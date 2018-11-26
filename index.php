<?php
session_start();
require('dbconnect.php');

//エラーを非表示化
error_reporting(0);

//idがセッションに記録されている，かつ，最後の行動から1時間以内
if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
    //ログインしているとき
    $_SESSION['time'] = time();
    
    $sql = sprintf('SELECT * FROM members WHERE id=%d',
        mysqli_real_escape_string($db, $_SESSION['id'])
    );
    $record = mysqli_query($db, $sql) or die(mysqli_connect_error($db));
    $member = mysqli_fetch_assoc($record);
} else {
    //ログインしていないとき
    //login.phpに画面遷移
    header('Location: login.php');
    exit();
}

//投稿を記憶する
//$_POSTに値が入っているか，つまりフォームから送信されたかの確認
if (!empty($_POST)) {
    //messageが空でないことを確認
    if ($_POST['message'] != '') {
        $sql = sprintf('INSERT INTO posts SET member_id=%d, message="%s", reply_post_id=%d, created=NOW()',
            mysqli_real_escape_string($db, $member['id']),
            mysqli_real_escape_string($db, $_POST['message']),
            mysqli_real_escape_string($db, $_POST['reply_post_id'])
        );
        mysqli_query($db, $sql) or die(mysqli_connect_error($db));
        //headerファンクションでindex.phpに遷移することにより二重投稿できないようにしている
        header('Location: index.php');
        exit();
    }
}

//投稿を取得する
$page = $_REQUEST['page'];
if ($page == '') {
    $page = 1;
}
$page = max($page, 1);

//最終ページを取得する
$sql = 'SELECT COUNT(*) AS cnt FROM posts';
$recordSet = mysqli_query($db, $sql);
$table = mysqli_fetch_assoc($recordSet);
$maxPage = ceil($table['cnt'] / 5);
$page = min($page, $maxPage);

$start = ($page - 1) * 5;
$start = max(0, $start);


//投稿を取得する
$sql = sprintf('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT %d, 5', $start);
$posts = mysqli_query($db, $sql) or die(mysqli_connect_error($db));

//返信の場合
if (isset($_REQUEST['res'])) {
    $sql = sprintf('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=%d ORDER BY p.created DESC',
        mysqli_real_escape_string($db, $_REQUEST['res'])
    );
    $record = mysqli_query($db, $sql) or die(mysqli_connect_error($db));
    $table = mysqli_fetch_assoc($record);
    $message = '@'.$table['name'].''.$table['message'];
}

//htmlspecialchars()のショートカット
function h($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'TTF-8');
}

//メッセージ内のURLにリンクを設定する
//https://qiita.com/sukobuto/items/b6cdfa966b29823c62f0　を参照
function url2link($body, $link_title = null)
{
    $pattern = '/(href=")?https?:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:@&=+$,%#]+/';
    $body = preg_replace_callback($pattern, function($matches) use ($link_title) {
        // 既にリンクの場合や Markdown style link の場合はそのまま
        if (isset($matches[1])) return $matches[0];
        $link_title = $link_title ?: $matches[0];
        return "<a href=\"{$matches[0]}\">$link_title</a>";
    }, $body);
    return $body;
}

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
           <div>
               <a href="logout.php">ログアウト</a>
           </div>
            <form action="" method="post">
                <dl>
                    <dt><?php echo htmlspecialchars($member['name']); ?>さん，メッセージをどうぞ</dt>
                    <dd>
                        <textarea name="message" cols="50" rows="5"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></textarea>
                        <input type="hidden" name="reply_post_id" value="<?php echo htmlspecialchars($_REQUEST['res'], ENT_QUOTES, 'UTF-8'); ?>" />
                    </dd>
                </dl>
                <div>
                   <p><input type="submit" value="投稿する" /></p>
                </div>
            </form>
            
            <?php while($post = mysqli_fetch_assoc($posts)): ?>
            <div class="msg">
                <p></p>
                <!--プロフィール画像-->
                <img src="member_picture/<?php echo h($post['picture']); ?>" alt="<?php echo h($post['name']); ?>" width="48" height="48" />
                
                <!--メッセージ-->
                <?php echo url2link(h($post['message'])); ?><span class="name">(<?php echo h($post['name']); ?>)</span>
                
                <!--返信用のメッセージ引用ボタン「Re」-->
                [<a href="index.php?res=<?php echo h($post['id']); ?>">Re</a>]
                
                <!--投稿日時-->
                <a href="view.php?id=<?php echo h($post['id']); ?>"><?php echo h($post['created']); ?></a>
                
                <!--メッセージを引用している場合は，引用元のメッセージリンクを表示-->
                <?php if ($post['reply_post_id'] > 0): ?>
                    <a href="view.php?id=<?php echo h($post['reply_post_id']); ?>">返信元のメッセージ</a>
                <?php endif; ?>
                
                <!--
                    削除ボタン
                    セッション内のidと投稿メッセージのmember_idが等しい時に削除ボタンを表示
                    つまり，投稿者しか削除できない
                -->
                <?php if ($_SESSION['id'] == $post['member_id']): ?>
                    [<a href="delete.php?id=<?php echo h($post['id']); ?>" style="color: #F33;">削除</a>]
                <?php endif; ?>
                
            </div>
            <?php endwhile ?>
            
            <ul class="paging">
                <?php if ($page > 1): ?>
                    <li>
                        <a href="index.php?page=<?php print($page - 1); ?>">前のページへ</a>
                    </li>
                <?php else: ?>
                    <li>前にページへ</li>
                <?php endif; ?>
                <?php if ($page < $maxPage): ?>
                    <li>
                        <a href="index.php?page=<?php print($page + 1); ?>">次のページへ</a>
                    </li>
                <?php else: ?>
                    <li>次のページへ</li>
                <?php endif; ?>
            </ul>
        </div>
        <div id="foot">
            <p>(C) H2O SPACE, Mynavi</p>
        </div>
    </div>
</body>

</html>
