<?php

session_start();

//ログインしていない場合、login.phpを表示
if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

require_once('db.php');
require_once('functions.php');

/**
 * @param String $tweet_textarea
 * つぶやき投稿を行う。
 */
function newtweet($tweet_textarea)
{
  // 汎用ログインチェック処理をルータに作る。早期リターンで
  createTweet($tweet_textarea, $_SESSION['user_id']);
}
/**
 * @param String $tweet_textarea
 * @param String $reply_post_id
 * 返信を行う。
 */
function newReplyTweet($tweet_textarea, $reply_post_id)
{
  createReply($tweet_textarea, $_SESSION['user_id'], $reply_post_id);
}
/**
 * ログアウト処理を行う。
 */
function logout()
{
  $_SESSION = [];
  $msg = 'ログアウトしました。';
}

if ($_POST) { /* POST Requests */
  if (isset($_POST['logout'])) { //ログアウト処理
    logout();
    header("Location: login.php");
  } else if (isset($_POST['tweet_textarea'])) { //投稿処理
    if (isset($_POST['reply_post_id'])) {
      /* 返信である */
      newReplyTweet($_POST['tweet_textarea'], $_POST['reply_post_id']);
    } else {
      /* 返信でない */
      newtweet($_POST['tweet_textarea']);
    }
  }
  header("Location: index.php");
}

if ($_GET) {
  /* クエリストリングがある */
  if (isset($_GET['fav_post_id'])) {
    /* いいねボタンが押された */
    $fav_post_id = $_GET['fav_post_id']; // 今いいねされた投稿id
    $my_user_id = $_SESSION['user_id']; // 自分のuser_id
    /* この投稿は自分が... */
    if (getFavoritesByMemberIdAndPostId($my_user_id, $fav_post_id)) {
      /* 既にいいねしている */
      deleteFavorite($my_user_id, $fav_post_id); // いいねを消す
    } else {
      /* まだいいねしていない */
      createFavorite($my_user_id, $fav_post_id); // いいねを付ける
    }
    header("Location: index.php");
  }
}

$tweets = getTweets();
$tweet_count = count($tweets);
$favorites = getFavorites();
?>

<!DOCTYPE html>
<html lang="ja">

<?php require_once('head.php'); ?>

<body>
  <div class="container">
    <h1 class="my-5">新規投稿</h1>
    <div class="card mb-3">
      <div class="card-body">
        <form method="POST">
          <!-- 返信課題はここからのコードを修正しましょう。 -->
          <textarea class="form-control" type=textarea name="tweet_textarea" ?><?= isset($_GET['reply']) ? getUserReplyText($_GET['reply']) : '' ?></textarea>
          <?php if (isset($_GET['reply'])) { ?>
            <input type="hidden" name="reply_post_id" value="<?= $_GET['reply'] ?>" />
          <?php } ?>
          <!-- 返信課題はここからのコードを修正しましょう。 -->
          <br>
          <input class="btn btn-primary" type=submit value="投稿">
        </form>
      </div>
    </div>
    <h1 class="my-5">コメント一覧</h1>
    <?php foreach ($tweets as $t) { ?>
      <div class="card mb-3">
        <div class="card-body">
          <p class="card-title"><b><?= "{$t['id']}" ?></b> <?= "{$t['name']}" ?> <small><?= "{$t['updated_at']}" ?></small></p>
          <p class="card-text"><?= "{$t['text']}" ?></p>
          <!--返信課題はここから修正しましょう。-->
          <?php if (isAlreadyFavorited($favorites, $_SESSION['user_id'], $t['id'])) { ?>
            <a href="?fav_post_id=<?= "{$t['id']}" ?>"><img class="favorite-image" src='/images/heart-solid-red.svg'></a>
          <?php } else { ?>
            <a href="?fav_post_id=<?= "{$t['id']}" ?>"><img class="favorite-image" src='/images/heart-solid-gray.svg'></a>
          <?php } ?>
          <font color="#33CCFF"><b><?= cntFavorite($favorites, $t['id']) ?></b></font><br>
          <p>
            <a href="index.php?reply=<?= "{$t['id']}" ?>">[返信する]</a>
            <?php if (isset($t['reply_id'])) { ?>
              <a href="/view.php?id=<?= "{$t['reply_id']}" ?>">[返信元のメッセージ]</a>
            <?php } ?>
          </p>
          <!--返信課題はここまで修正しましょう。-->
        </div>
      </div>
    <?php } ?>
    <form method="POST">
      <input type="hidden" name="logout" value="dummy">
      <button class="btn btn-primary">ログアウト</button>
    </form>
    <br>
  </div>
</body>

</html>
