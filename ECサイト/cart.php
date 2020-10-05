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
$msg = array();  //メッセージ
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];


// コネクション取得
if ($link = mysqli_connect($host, $user, $passwd, $dbname)) {
    
    // 文字コードセット
    mysqli_set_charset($link, 'utf8');


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

/*--------------カートから商品を削除--------------*/
        if ($_POST['sql_kind'] === 'delete_cart') {
            
            //商品IDをitem_idに格納
            $item_id = $_POST['item_id'];
            
            //商品をitem_tableから削除するSQL
            $delete_cart_sql = "DELETE FROM ec_cart_table
                                WHERE ec_cart_table.item_id = {$item_id}
                                AND ec_cart_table.user_id = {$user_id}";
    
            mysqli_query($link, $delete_cart_sql);
            $msg[] = 'カートから削除しました。';
        }

        
/*--------------数量の変更--------------*/
        if ($_POST['sql_kind'] === 'change_cart') {
            
            //更新後の購入数を変数 $amount に格納
            if (isset($_POST['select_amount']) === true) {
                $amount = $_POST['select_amount'];
                if (preg_match("/^([1-9][0-9]*)$/", $amount) !== 1) {
                    $err_msg[] = '数量は1以上の半角数字を入力してください。';
                } else if (mb_strlen($amount) === 0) {
                    $err_msg[] = '数量を入力してください。';
                } 
            }            
            
            //アイテムIDを変数$item_idに格納
            $item_id = $_POST['item_id'];
            
            //ec_stock_tableの数量を変更するSQL            
            $update_ec_cart_table = "UPDATE ec_cart_table
                                     SET amount = {$amount}
                                     WHERE ec_cart_table.item_id = {$item_id}
                                     AND ec_cart_table.user_id = {$user_id}";

            //クエリ実行
            if (count($err_msg) === 0) {
                mysqli_query($link, $update_ec_cart_table);
                $msg[] = '数量を変更しました。';
            }
            
            
        }
    }
    
    //カート情報を取得するSQL
    $cart_list_sql = "SELECT img, ec_cart_table.item_id, item_name, price, amount
                      FROM ec_cart_table
                      JOIN ec_item_table
                      ON ec_item_table.item_id = ec_cart_table.item_id
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
            $i++;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ショッピングカートページ</title>
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
        <h1 class="title">ショッピングカート</h1>



    <div class="cart-list-title">
      <span class="cart-list-price">価格</span>
      <span class="cart-list-num">数量</span>
    </div>
    <ul class="cart-list">
<?php if (isset($item_list) !== true) { ?>
<?php       $err_msg[] = 'カートに商品がありません。'; ?>
<?php } else {?>
<?php $sum = 0; ?>
<?php   foreach($item_list as $value) {?>
      <li>
        <div class="cart-item">
        <img class="cart-item-img" src="./img/<?php echo $value['img']; ?>" >
          <span class="cart-item-name"><?php echo $value['item_name']; ?></span>
          <form class="cart-item-del" action="./cart.php" method="post">
            <input type="submit" value="削除">
            <input type="hidden" name="item_id" value="<?php echo $value['item_id']; ?>">
            <input type="hidden" name="sql_kind" value="delete_cart">
          </form>
          <span class="cart-item-price">¥<?php echo $value['price']; ?></span>
          <form class="form_select_amount" id="form_select_amount<?php echo $value['item_id']; ?>" action="./cart.php" method="post">
            <input type="text" class="cart-item-num2" min="0" name="select_amount" value="<?php echo $value['amount']; ?>">個&nbsp;<input type="submit" value="変更する">
            <input type="hidden" name="item_id" value="<?php echo $value['item_id']; ?>">
            <input type="hidden" name="sql_kind" value="change_cart">
          </form>
        </div>
      </li>
<?php $sum += $value['price'] * $value['amount']; ?>
<?php   }?>
<?php }?>
    </ul>
    <div class="buy-sum-box">
      <span class="buy-sum-title">合計</span>
      <span class="buy-sum-price">¥ <?php if (isset($sum) === true) {echo $sum;}  else {echo '0';} ?></span>
    </div>
    <div>
<?php   foreach($msg as $value) {
            echo '<p>'.$value.'</p>';
}
        foreach($err_msg as $value) {
            echo '<p class="err-msg">'.$value.'</p>';
} ?>
<?php if (isset($sum) === true) { ?>
      <form action="./finish.php" method="post">
        <input class="buy-btn" type="submit" value="購入する">
      </form>
<?php }?>
    </div>
    </div>
</body>
</html>
