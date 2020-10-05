<?php
session_start();
if($_SESSION['user_id'] === 'admin') {
    header('Location:./admin.php');
}else if(isset($_SESSION['user_id']) === false){
    header('Location:./login.php');
}
$host   = 'localhost';     // データベースのホスト名又はIPアドレス
$user   = 'codecamp29225'; // MySQLのユーザ名
$passwd = 'GHPOIPML';      // MySQLのパスワード
$dbname = 'codecamp29225'; // データベース名
$err_msg     = array();   // エラーメッセージ
$item_list   = array();   //商品リスト
$date = date('Y-m-d H:i:s'); // 日時
$user_id = $_SESSION['user_id']; //ユーザーID
$user_name = $_SESSION['user_name'];

// コネクション取得
if ($link = mysqli_connect($host, $user, $passwd, $dbname)) {
    
    // 文字コードセット
    mysqli_set_charset($link, 'utf8');


    //商品情報を取得するSQL
    $item_list_sql = 'SELECT img, ec_item_table.item_id, item_name, price, stock, status
                       FROM ec_item_table
                       JOIN ec_stock_table
                       ON ec_item_table.item_id = ec_stock_table.item_id';
    // クエリ実行
    if ($result_item_list = mysqli_query($link, $item_list_sql)) {
        $i = 0;
        while ($row = mysqli_fetch_assoc($result_item_list)) {
            $item_list[$i]['img']         = htmlspecialchars($row['img'],         ENT_QUOTES, 'UTF-8');
            $item_list[$i]['item_id']     = htmlspecialchars($row['item_id'],    ENT_QUOTES, 'UTF-8');
            $item_list[$i]['item_name']   = htmlspecialchars($row['item_name'],  ENT_QUOTES, 'UTF-8');
            $item_list[$i]['price']       = htmlspecialchars($row['price'],       ENT_QUOTES, 'UTF-8');
            $item_list[$i]['stock']       = htmlspecialchars($row['stock'],       ENT_QUOTES, 'UTF-8');
            $item_list[$i]['status']      = htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8');
            $i++;
        }
    }
    
   mysqli_free_result($result_item_list);


   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
       $item_id = $_POST['item_id'];
       
       //カートに商品をinsertするSQL
        $insert_new_item = "INSERT INTO ec_cart_table(user_id, item_id, amount, created_date, updated_date)
                            VALUES({$user_id}, {$item_id}, 1, '{$date}', '{$date}')
                            ON duplicate key update amount = amount+1, updated_date = now()";

        $result = mysqli_query($link, $insert_new_item);
       
       
   }
   
   
}                       
mysqli_close($link);
               
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>商品一覧ページ</title>
    <link type="text/css" rel="stylesheet" href="./css/common.css">
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
</head>
<body>
  <header>
    <div class="header-box">
      <a href="./top.php">
        <img class="logo" src="./logo/codeshop_logo.png" alt="CodeSHOP">
      </a>
      <a class="nemu" href="./logout.php">ログアウト</a>
      <a href="./cart.php" class="cart"><i class="fas fa-shopping-cart"></i></a>
      <p class="nemu">ユーザー名：<?php echo $_SESSION['user_name']; ?></p>
    </div>
  </header>
  <div class="content">
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['sql_kind'] === 'insert_cart') {
        echo '<p>商品をカートに追加しました。</p>';
    }
}
?>
    <ul class="item-list">
<?php foreach($item_list as $value) { ?>
        <li>
            <div class="item">
                <form action="./top.php" method="post">
<?php               if ($value['status'] === "1") { ?>
                        <img class="item-img" src="./img/<?php print $value['img']; ?>" >
                        <div class="item-info">
                            <span class="item-name"><?php echo $value['item_name']; ?></span>
                            <span class="item-price"><?php echo $value['price']; ?>円</span>
                        </div>
                        <input class="cart-btn" type="submit" value="カートに入れる">
<?php               } ?>
<?php               if ($value['stock'] === "0"){
                        echo '<p class="sold-out">売り切れ</p>'; ?>
                        <input type="hidden" name="item_id" value="<?php echo $value['item_id']; ?>">
<?php               } else { ?>
                        <input type="hidden" name="item_id" value="<?php echo $value['item_id']; ?>">
                        <input type="hidden" name="sql_kind" value="insert_cart">
<?php               } ?>

              </form>
            </div>
        </li>
<?php } ?>         
    </ul>
 
  </div>
</body>
</html>
