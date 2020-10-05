<?php

$host   = 'localhost';     // データベースのホスト名又はIPアドレス
$user   = 'codecamp29225'; // MySQLのユーザ名
$passwd = 'GHPOIPML';      // MySQLのパスワード
$dbname = 'codecamp29225'; // データベース名
$err_msg      = array();   // エラーメッセージ
$user_list   = array();   // 商品一覧
$date = date('Y-m-d H:i:s'); // 日時


// コネクション取得
if ($link = mysqli_connect($host, $user, $passwd, $dbname)) {
 
   // 文字コードセット
   mysqli_set_charset($link, 'UTF8');
   
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        //ユーザー名を $user_name に格納
        if (isset($_POST['user_name']) === true) {
            $user_name = trim($_POST['user_name']);
            if(preg_match("/^([a-zA-Z0-9]{6,})$/", $user_name) !== 1) {
                $err_msg[] = 'ユーザー名は6文字以上の半角英数字を入力してください。';
            } else if(mb_strlen($user_name) === 0){
                $err_msg[] = '名前を入力してください。';
            }
        }        
        //パスワードを $password に格納
        if (isset($_POST['password']) === true) {
            $password = $_POST['password'];
            if(preg_match("/^([a-zA-Z0-9]{6,})$/", $password) !== 1) {
                $err_msg[] = 'パスワードは6文字以上の半角英数字を入力してください。';
            } else if (mb_strlen($password) === 0) {
                $err_msg[] = 'パスワードを入力してください。';
            } 
        }
        
        //ユーザー名の重複をチェックするSQL
        $check = "SELECT COUNT(*) AS cnt FROM ec_user_table WHERE user_name = '{$user_name}'";

        //クエリ実行
        $check_result = mysqli_query($link, $check);
        
        $row = mysqli_fetch_assoc($check_result);
        
        //ユーザー名重複はエラーメッセージを表示
        if($row['cnt'] !== "0") {
            $err_msg[] = '同じユーザー名が既に登録されています';
        }
        
        //新規ユーザーをinsertするSQL
        $insert_new_user = "INSERT INTO ec_user_table(user_name, password, created_date, updated_date)
                            VALUES('{$user_name}', '{$password}', '{$date}', '{$date}')";
                            
        //クエリ実行
        if(count($err_msg) === 0) {
            mysqli_query($link, $insert_new_user);
            $msg = 'アカウント作成を完了しました';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>ユーザ登録ページ</title>
  <link type="text/css" rel="stylesheet" href="./css/common.css">
</head>
<body>
    <header>
        <div class="header-box">
            <a href="./top.php">
                <img class="logo" src="./logo/codeshop_logo.png" alt="CodeSHOP">
            </a>
            <a href="./cart.php" class="cart"></a>
        </div>
    </header>
    <div class="content">
    <div class="register">
        
<?php
if(isset($msg) === true) {
    print '<p style="color:blue">'.$msg.'</p>';
} else {
    foreach($err_msg as $value){
        print '<p style="color:red">'.$value.'</p>';
    }
}
?>
        <form method="post" action="./register.php">
            <div>ユーザー名：<input type="text" name="user_name" placeholder="ユーザー名"></div>
            <div>パスワード：<input type="password" name="password" placeholder="パスワード">
            <div><input type="submit" value="ユーザーを新規作成する">
          </form>
    <div class="login-link"><a href="./login.php">ログインページに移動する</a></div>
    </div>
  </div>
</body>
</html>