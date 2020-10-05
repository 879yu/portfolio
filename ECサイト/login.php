<?php
session_start();
$host   = 'localhost';     // データベースのホスト名又はIPアドレス
$user   = 'codecamp29225'; // MySQLのユーザ名
$passwd = 'GHPOIPML';      // MySQLのパスワード
$dbname = 'codecamp29225'; // データベース名
$err_msg     = array();   // エラーメッセージ

// コネクション取得
if ($link = mysqli_connect($host, $user, $passwd, $dbname)) {
    
   // 文字コードセット
   mysqli_set_charset($link, 'utf8');
   
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
       
       $user_name = $_POST['user_name'];
       $password = $_POST['password'];
       
       if($user_name === 'admin' && $password === 'admin') {
           $_SESSION['user_id'] = 'admin';
           $_SESSION['user_name'] = 'admin';
           header('Location:./admin.php');
       }
        
        //ユーザー名とパスワードをSELECTするSQL
        $query = "SELECT user_id FROM ec_user_table
                  WHERE user_name = '{$user_name}' AND password = '{$password}'";
    
        $result = mysqli_query($link, $query);
        
        $row = mysqli_fetch_assoc($result);
        
        //ユーザー名かパスワード違いはエラーメッセージを表示
        if(isset($row['user_id']) === false) {
            $err_msg[] = 'ユーザー名かパスワードが異なります';
        } else {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_name'] = $user_name;
            header('Location:./top.php');
        }       
   }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ログインページ</title>
    <link type="text/css" rel="stylesheet" href="./css/common.css">
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
</head>
<body>
  <header>
    <div class="header-box">
      <a href="./top.php">
        <img class="logo" src="./logo/codeshop_logo.png" alt="CodeSHOP">
      </a>
      <a href="./cart.php" class="cart"><i class="fas fa-shopping-cart"></i></a>
    </div>
  </header>
  <div class="content">
    <div class="login">
<?php   foreach($err_msg as $value) {
            print '<p class="err-msg">'.$value.'</p>'; }?>
      <form method="post" action="./login.php">
        <div><input type="text" name="user_name" placeholder="ユーザー名"></div>
        <div><input type="password" name="password" placeholder="パスワード">
        <div><input type="submit" value="ログイン">
      </form>
      <div class="account-create">
        <a href="./register.php">ユーザーの新規作成</a>
      </div>
    </div>
  </div>
</body>
</html>
