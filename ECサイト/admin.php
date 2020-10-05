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
$err_msg      = array();   // エラーメッセージ
$item_list   = array();   // 商品一覧
$date = date('Y-m-d H:i:s'); // 日時

// コネクション取得
if ($link = mysqli_connect($host, $user, $passwd, $dbname)) {
 
   // 文字コードセット
   mysqli_set_charset($link, 'UTF8');
   
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
/*-----------ここから商品追加処理------------*/
        
        if ($_POST['sql_kind'] === 'insert') {
        
            //名前を $name に格納
            if (isset($_POST['new_name']) === true) {
                $name = $_POST['new_name'];
                $name = preg_replace('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $name);
                if(mb_strlen($name) === 0){
                    $err_msg[] = '名前を入力してください。';
                }
            }
        
            //値段を $price に格納
            if (isset($_POST['new_price']) === true) {
                $price = $_POST['new_price'];
                if(preg_match("/^([1-9][0-9]*|0)$/", $price) !== 1) {
                    $err_msg[] = '値段は半角数字を入力してください。';
                } else if (mb_strlen($price) === 0) {
                    $err_msg[] = '値段を入力してください。';
                } 
            }
                
            //個数を $stock に格納
            if (isset($_POST['new_stock']) === true) {
                $stock = $_POST['new_stock'];
                 if (preg_match("/^([1-9][0-9]*|0)$/", $stock) !== 1) {
                    $err_msg[] = '個数は半角数字を入力してください。';
                } else if (mb_strlen($stock) === 0) {
                    $err_msg[] = '個数を入力してください。';
                }
            }
            
            //ランダムな文字列を生成する関数（画像の名前用）
            function rand_str($length) {
                $str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
                $r_str = null;
                for ($i = 0; $i < $length; $i++) {
                    $r_str .= $str[rand(0, count($str) - 1)];
                }
                return $r_str;
            }

            //ファイルに名前をつけてディレクトリ img に移動
            if (mb_strlen($_FILES['new_img']['name']) !== 0) {
                $ext = mime_content_type($_FILES['new_img']['tmp_name']);
                //JPEGかPNGのとき、拡張子を $ext に格納
                if($ext === "image/jpeg"){
                    $ext = 'jpg';
                } else if ($ext === "image/png"){
                    $ext = 'png';
                }
                // ファイルの拡張子を判定し、jpgとpng以外はエラーメッセージを表示
                if ($ext === 'jpg' || $ext === 'png') {
                    //ランダムな20文字のファイル名と拡張子をつける
                    $filename = rand_str(20).".".$ext;
                    move_uploaded_file($_FILES['new_img']['tmp_name'], './img/'.$filename);
                } else {
                    $filename = '';
                    $err_msg[] = 'ファイル形式が異なります。画像ファイルはJPEGまたはPNGのみ利用可能です。';
                }
            } else {
                $filename = '';
                $err_msg[] = 'ファイルをアップロードしてください。';
            }
            
            // 非公開は0、公開は1を変数 $status に格納
            if (isset($_POST['new_status']) === true) {
                $status = $_POST['new_status'];
                if($status !== "0" && $status !== "1"){
                    $err_msg[] = '不正な処理です。';
                }
            }
            
            // 更新系の処理を行う前にトランザクション開始(オートコミットをオフ）
            mysqli_autocommit($link, false);
        
            // 新規商品をinsertするSQL
            $insert_new_item = "INSERT INTO ec_item_table(item_name, price, img, created_date, updated_date, status)
                                VALUES('{$name}', {$price}, '{$filename}', '{$date}', '{$date}', {$status})";

            // クエリ実行
            if(mysqli_query($link, $insert_new_item) !== true) {
                $err_msg[] ='追加に失敗しました。'; 
                
            } else {
                // $insert_new_item で生成されたitem_idを変数に格納
                $item_id = mysqli_insert_id($link);
                
                // 新規商品の在庫数をinsertするSQL
                $insert_new_stock = "INSERT INTO ec_stock_table(item_id, stock, created_date, updated_date)
                                     VALUES({$item_id}, {$stock}, '{$date}', '{$date}')";
                // クエリ実行
                if(mysqli_query($link, $insert_new_stock) !== true){
                    $err_msg[] ='追加に失敗しました。'; 
                }
            }
            
            // トランザクション成否判定
            if (count($err_msg) === 0) {
                // 処理確定
                mysqli_commit($link);
                //成功の場合、メッセージを表示
                echo '<p>追加成功</p>';
            } else {
                // 処理取消
                mysqli_rollback($link);
            }
        
        
/*-----------ここから在庫の更新------------*/

        } else if($_POST['sql_kind'] === 'update') {
            
            //更新後の在庫数を変数 $update_stock に格納
            if (isset($_POST['update_stock']) === true) {
                $update_stock = $_POST['update_stock'];
                if (preg_match("/^([1-9][0-9]*|0)$/", $update_stock) !== 1) {
                    $err_msg[] = '個数は半角数字を入力してください。';
                } else if (mb_strlen($update_stock) === 0) {
                    $err_msg[] = '個数を入力してください。';
                } 
            }
            
            //アイテムIDを変数$item_idに格納
            $item_id = $_POST['item_id'];
            
            // 更新系の処理を行う前にトランザクション開始(オートコミットをオフ）
            mysqli_autocommit($link, false);            
            
            //ec_stock_tableの在庫数と更新日を変更するSQL            
            $update_ec_stock_table = "UPDATE ec_stock_table
                                   SET stock = {$update_stock}, updated_date = '{$date}'
                                   WHERE ec_stock_table.item_id = {$item_id}";

            //ec_item_tableの更新日を変更するSQL
            $update_ec_item_table = "UPDATE ec_item_table
                                   SET updated_date = '{$date}'
                                   WHERE ec_item_table.item_id = {$item_id}";
            
            //クエリ実行
            if(mysqli_query($link, $update_ec_stock_table) !== true || mysqli_query($link, $update_ec_item_table) !== true) {
                $err_msg[] ='更新できませんでした。'; 
            }
            
            // トランザクション成否判定
            if (count($err_msg) === 0) {
               // 処理確定
               mysqli_commit($link);
                //成功の場合、メッセージを表示
                echo '<p>在庫数を更新しました。</p>';
            } else {
                // 処理取消
                mysqli_rollback($link);
            }
            
/*-----------ここからステータスの更新------------*/
            
        } else if ($_POST['sql_kind'] === 'change') {
            
            // 非公開は0、公開は1を変数 $status に格納
            if (isset($_POST['new_status']) === true) {
                // ステータスが0か1以外の時、エラーメッセージを表示
                if ($status !== "0" && $status !== "1") {
                    $err_msg[] = '不正な処理です。';
                }
            } else {
                $status = $_POST['change_status'];
            }

            // ステータス0のときは1、1のときは0に更新
            if ($status === "0") {
                $status = "1";
            } else if ($status === "1") {
                $status = "0";
            }

            // ステータスを更新するSQL
            $change_status = "UPDATE ec_item_table
                              SET status = {$status}
                              WHERE ec_item_table.item_id = {$_POST['item_id']}";
                              
           // エラー無しのとき、クエリ実行
            if (count($err_msg) === 0) {
                mysqli_query($link, $change_status);
                echo '<p>ステータスを更新しました。</p>';
            } else {
                // 処理取消
                mysqli_rollback($link);  
            }
        
/*-----------ここから商品削除の操作------------*/
            
        } else if ($_POST['sql_kind'] === 'delete') {
        
        //商品IDをitem_idに格納
        $item_id = $_POST['item_id'];
        
        //商品をitem_tableから削除するSQL
        $delete_item = "DELETE FROM ec_item_table
                        WHERE ec_item_table.item_id = {$item_id}";
            
        //商品をstock_tableから削除するSQL
        $delete_stock = "DELETE FROM ec_stock_table
                         WHERE ec_stock_table.item_id = {$item_id}";
        
        //クエリ実行
        mysqli_query($link, $delete_item);
        mysqli_query($link, $delete_stock);
        echo '<p>商品を削除しました。</p>';
        
            
        }
    }
}
//var_dump($err_msg); // エラーの確認が必要ならばコメントを外す

    //商品情報を取得するSQL
    $item_list_sql = 'SELECT img, ec_item_table.item_id, item_name, price, stock, status FROM ec_item_table JOIN ec_stock_table ON ec_item_table.item_id = ec_stock_table.item_id';

    //クエリ実行
   if ($result_item_list = mysqli_query($link, $item_list_sql)) {
       $i = 0;
       while ($row = mysqli_fetch_assoc($result_item_list)) {
           $item_list[$i]['img']        = htmlspecialchars($row['img'],        ENT_QUOTES, 'UTF-8');
           $item_list[$i]['item_id']    = htmlspecialchars($row['item_id'],    ENT_QUOTES, 'UTF-8');
           $item_list[$i]['item_name']  = htmlspecialchars($row['item_name'],  ENT_QUOTES, 'UTF-8');
           $item_list[$i]['price']      = htmlspecialchars($row['price'],      ENT_QUOTES, 'UTF-8');
           $item_list[$i]['stock']      = htmlspecialchars($row['stock'],      ENT_QUOTES, 'UTF-8');
           $item_list[$i]['status']     = htmlspecialchars($row['status'],    ENT_QUOTES, 'UTF-8');
           $i++;
       }
   }
   
   mysqli_free_result($result_item_list);
   mysqli_close($link);


?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>商品管理ツール</title>
    <link rel="stylesheet" href="./css/admin.css">
</head>
<body>

<?php   //エラーメッセージを表示
        foreach($err_msg as $value){
            echo '<p>'.$value.'</p>';
        }
?>    

    <h1>CodeSHOP管理ページ</h1>
    <div>
        <a class="nemu" href="./logout.php">ログアウト</a>
        <a href="./admin_user.php">ユーザー管理ページ</a>
    </div>    
    <section>
        <h2>新規商品追加</h2>
        <form method="post" enctype="multipart/form-data">
            <div><label>名前: <input type="text" name="new_name" value=""></label></div>
            <div><label>値段: <input type="text" name="new_price" value=""></label></div>
            <div><label>個数: <input type="text" name="new_stock" value=""></label></div>
            <div><input type="file" name="new_img"></div>
            <div>
                <select name="new_status">
                    <option value="0">非公開</option>
                    <option value="1">公開</option>
                </select>
            </div>
            <input type="hidden" name="sql_kind" value="insert">
            <div><input type="submit" value="■□■□■商品追加■□■□■"></div>
        </form>
    </section>
    <section>
        <h2>商品情報の一覧・変更</h2>
        <table>
            <tr>
                <th>商品画像</th>
                <th>商品名</th>
                <th>価格</th>
                <th>在庫数</th>
                <th>ステータス</th>
                <th>操作</th>
            </tr>
<?php           foreach($item_list as $value) { ?>
            <tr>
                <form method="post">
                    <td><img width="100px" src="./img/<?php echo $value['img']; ?>"></td>
                    <td class="item_name_width"> <?php echo $value['item_name']; ?> </td>
                    <td class="text_align_right"> <?php echo $value['price'].'円'; ?> </td>
                    <td><input type="text" class="input_text_width text_align_right" name="update_stock" value="<?php echo $value['stock']; ?>"><input type="submit" value="変更"></td>
                    <input type="hidden" name="item_id" value="<?php echo $value['item_id']; ?>">
                    <input type="hidden" name="sql_kind" value="update"> 
                </form>
                <form method="post">
                    <td><input type="submit" value="<?php if ($value['status'] === "0"){ echo '非公開 → 公開'; } else if ($value['status'] === "1") { echo '公開 → 非公開'; } ?>" ></td>
                    <input type="hidden" name="change_status" value="<?php echo $value['status']; ?>">
                    <input type="hidden" name="item_id" value="<?php echo $value['item_id']; ?>">
                    <input type="hidden" name="sql_kind" value="change">
                </form>
                <form method="post">
                    <td><input type="submit" value="削除する"></td>
                    <input type="hidden" name="item_id" value="<?php echo $value['item_id']; ?>">
                    <input type="hidden" name="sql_kind" value="delete">
                </form>
            </tr>
<?php           } ?>
        </table>
    </section>
</body>
</html>
