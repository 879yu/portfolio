<?php
session_start();
if(isset($_SESSION['user_id']) === true && $_SESSION['user_id'] !== 'admin') {
    header('Location:./login.php');
} else if(isset($_SESSION['user_id']) === false){
    header('Location:./login.php');
}

$host   = 'localhost';     // データベースのホスト名又はIPアドレス
$user   = 'codecamp29225'; // MySQLのユーザ名
$passwd = 'GHPOIPML';      // MySQLのパスワード
$dbname = 'codecamp29225'; // データベース名
$err_msg     = array();   // エラーメッセージ
$user_list   = array();   // ユーザー一覧

// コネクション取得
if ($link = mysqli_connect($host, $user, $passwd, $dbname)) {
    
   // 文字コードセット
   mysqli_set_charset($link, 'utf8');

    //ユーザー情報を取得するSQL
    $user_list_sql = 'SELECT user_name, ec_user_table.created_date FROM ec_user_table';
    
    //クエリ実行
    if ($result_user_list = mysqli_query($link, $user_list_sql)) {
       $i = 0;
       while ($row = mysqli_fetch_assoc($result_user_list)) {
           $user_list[$i]['user_name']      = htmlspecialchars($row['user_name'],       ENT_QUOTES, 'UTF-8');
           $user_list[$i]['created_date']   = htmlspecialchars($row['created_date'],    ENT_QUOTES, 'UTF-8');
           $i++;
       }
    }

}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>ユーザー管理ページ</title>
  <link type="text/css" rel="stylesheet" href="./css/admin.css">
</head>
<body>
  <h1>CodeSHOP 管理ページ</h1>
  <div>
    <a class="nemu" href="./logout.php">ログアウト</a>
    <a href="./admin.php">商品管理ページ</a>
  </div>
  <h2>ユーザー情報一覧</h2>
  <table>
    <tr>
      <th>ユーザーID</th>
      <th>登録日</th>
    </tr>
<?php foreach($user_list as $value) { ?>
    <tr>
      <td class="name_width"><?php echo $value['user_name']; ?></td>
      <td ><?php echo $value['created_date']; ?></td>
    </tr>
<?php } ?>
