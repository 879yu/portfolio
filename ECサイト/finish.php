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
$msg = array(); //メッセージ
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$date = date('Y-m-d H:i:s'); // 日時

// コネクション取得
if ($link = mysqli_connect($host, $user, $passwd, $dbname)) {
    
    // 文字コードセット
    mysqli_set_charset($link, 'utf8');


    //カート情報を取得するSQL
    $cart_list_sql = "SELECT img, ec_cart_table.item_id, item_name, price, amount, stock, status
                      FROM ec_cart_table
                      JOIN ec_item_table
                      ON ec_item_table.item_id = ec_cart_table.item_id
                      JOIN ec_stock_table
                      ON ec_item_table.item_id = ec_stock_table.item_id
                      WHERE ec_cart_table.user_id = {$user_id}";
    // クエリ実行
    if ($result = mysqli_query($link, $cart_list_sql)) {
        $i = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $item_list[$i]['img']         = htmlspecialchars($row['img'],       ENT_QUOTES, 'UTF-8');
            $item_list[$i]['item_id']     = htmlspecialchars($row['item_id'],   ENT_QUOTES, 'UTF-8');
            $item_list[$i]['item_name']   = htmlspecialchars($row['item_name'], ENT_QUOTES, 'UTF-8');
            $item_list[$i]['price']       = htmlspecialchars($row['price'],     ENT_QUOTES, 'UTF-8');
            $item_list[$i]['amount']      = htmlspecialchars($row['amount'],    ENT_QUOTES, 'UTF-8');
            $item_list[$i]['stock']      = htmlspecialchars($row['stock'],    ENT_QUOTES, 'UTF-8');
            if ($row['stock'] < $row['amount']) {
                $err_msg[] = $item_list[$i]['item_name'].'の在庫が不足しております。';
            }
            if ($row['status'] === "0") {
                $err_msg[] = $item_list[$i]['item_name'].'は非公開です。';
            } 
            $i++;
        }
    }
    mysqli_free_result($result);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        mysqli_autocommit($link, false);
        
        //購入された商品をec_cart_tableから削除するSQL
        $delete_cart = "DELETE FROM ec_cart_table
                        WHERE ec_cart_table.user_id = {$user_id}";
                        
        if (mysqli_query($link, $delete_cart) === false) {
            $err_msg[] = '購入に失敗しました。';
        }   
        
        //ec_stock_tableのstockを減らすSQL
        foreach ($item_list as $item) {
            $update_stock = "UPDATE ec_stock_table
                             SET stock = stock - {$item['amount']}, updated_date = '{$date}'
                             WHERE item_id = {$item['item_id']}";
 
            if(mysqli_query($link, $update_stock) === false) {
                $err_msg[] = '購入に失敗しました。';
            }
        }
        
        $err_msg = array_unique($err_msg);
        
        if (count($err_msg) === 0) {
            mysqli_commit($link);
        } else {
            mysqli_rollback($link);
        }

        
    }
    
    
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>購入完了ページ</title>
    <link type="text/css" rel="stylesheet" href="./css/common.css">
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
  
</head>
<body>
  <header>
    <div class="header-box">
      <a href="./top.php">
        <img class="logo" src="./logo/codeshop_logo.png" alt="CodeCamp SHOP">
      </a>
      <a class="nemu" href="./logout.php">ログアウト</a>
      <a href="./cart.php" class="cart"><i class="fas fa-shopping-cart"></i></a>
      <p class="nemu">ユーザー名：<?php echo $user_name; ?></p>
    </div>
  </header>
  <div class="content">
    <div class="cart-list-title">
      <span class="cart-list-price">価格</span>
      <span class="cart-list-num">数量</span>
    </div>
      <ul class="cart-list">
<?php   if (isset($item_list) !== true) { ?>
<?php       $msg[] = 'カートに商品がありません。'; ?>
<?php   } else if (empty($err_msg) !== true) {?>
<?php   foreach ($err_msg as $value) { ?>
  <p class="err-msg"><?php echo $value; ?></p>
<?php   } ?>
<?php   } else { ?>
<?php $sum = 0; ?>
<?php       foreach($item_list as $value) {?>
              <li>
                <div class="cart-item">
                    <img class="cart-item-img" src="./img/<?php echo $value['img']; ?>" >
                    <span class="cart-item-name"><?php echo $value['item_name']; ?></span>
                    <span class="cart-item-price">¥<?php echo $value['price']; ?></span>
                    <span class="finish-amount"><?php echo $value['amount']; ?></span>
                </div>
              </li>
<?php $sum += $value['price'] * $value['amount']; ?>
<?php       }?>
<?php       $msg[] = 'ご購入ありがとうございました。'; ?>
         
      </ul>
<?php   foreach ($msg as $value) { ?>
  <p class="finish-msg"><?php echo $value; ?></p>
<?php   } ?>

    <div class="buy-sum-box">
      <span class="buy-sum-title">合計</span>
      <span class="buy-sum-price">¥ <?php if (isset($sum) === true) {echo $sum;} ?></span>
    </div>
<?php }?>     
  </div>
</body>
</html>
