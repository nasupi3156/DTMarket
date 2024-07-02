<?php
/**
 * ファイル名 : cart.php(カートページの処理を制御するコントローラー)
 */
namespace shopping;

require_once dirname(__FILE__) . '/Bootstrap.class.php';

use shopping\Bootstrap;
use shopping\lib\PDODatabase;
use shopping\lib\Session;
use shopping\lib\Item;
use shopping\lib\Cart;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db);
$itm = new Item($db);
$cart = new Cart($db);

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$ses->checkSession();
// セッションにセッションIDを設定する
$customer_no = $_SESSION['customer_no'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;


$crt_id = (isset($_GET['crt_id']) === true && preg_match('/^\d+$/', $_GET['crt_id']) === 1) ? $_GET['crt_id'] : '';


$item_id = '';
if (isset($_GET['item_id']) && preg_match('/^\d+$/', $_GET['item_id'])) {
  $item_id = $_GET['item_id'];
} elseif (isset($_POST['item_id']) && preg_match('/^\d+$/', $_POST['item_id'])) {
  $item_id = $_POST['item_id']; 
}

// qtySelectはなぜ必要か？数量変更を区別するためにコードの理解とメンテナンスをしやすくする為
// 数量変更
$qtySelect = (isset($_GET['qtySelect']) === true && preg_match('/^\d+$/',$_GET['qtySelect']) === 1) ? $_GET['qtySelect'] : '';

$price = isset($_POST['price']) &&  is_numeric($_POST['price']) ? (float)$_POST['price'] : 0;

$quantity = (isset($_POST['quantity']) && preg_match('/^\d+$/', $_POST['quantity'])) ? (int)$_POST['quantity'] : 1;

// qtyは数量
$qty = (isset($_GET['qty']) === true && preg_match('/^\d+$/',$_GET['qty']) === 1) ? $_GET['qty'] : '1';


// 商品をカートに追加するための処理。具体的には、与えられた顧客番号と商品IDを使用して、カートに商品を追加
// insCartData : カートに商品データを挿入する役割。
if ($item_id !== '') {
  $res = $cart->insCartData($customer_no, $item_id, $qty);
  //登録に失敗した場合、エラーページを表示する
  if ($res === false) {
    echo "商品購入に失敗しました。";
    exit();
  }
}

// カート内の商品の数量を更新する
// qtyとquantityは本来同じ値を持ってるので引数からメソッドに渡された時にquantityでも同じ
if ($qtySelect && $crt_id && $qty) {
   $cart->updateCartData($crt_id, $qty);
    echo true;
    exit();
  }

// crt_idが空ではない場合のみ処理
if ($crt_id !== '') {
  $res = $cart->delCartData($crt_id);
}

// 顧客番号でカート情報を取得する
$dataArr = $cart->getCartData($customer_no);

list($sumNum, $sumPrice) = $cart->getItemAndSumPrice($customer_no);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reorder'])) {
  $item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
  $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
  $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
 
  // $items = $cart->getReorderData($user_id);

if ($item_id > 0 && $quantity > 0 && $price > 0) {
  // カートにアイテム追加 
  $itemCart = $cart->addItemCart($customer_no, $item_id, $quantity, $qty, $price);
  
  if($itemCart) {
    header ('Location' . Bootstrap::ENTRY_URL . 'cart.php');
    exit();
    } else {
      echo "カートに追加できませんでした。";
    }
  } else {
    // バリデーション失敗
    echo "入力されたデータは不正です。";
  }
}  

$context = [
  'family_name' => !empty($_SESSION['family_name']) ? $_SESSION['family_name'] : 'ゲスト',
];
//数の合計
$context['sumNum'] = $sumNum; 
// 金額合計
$context['sumPrice'] = $sumPrice;
//カート情報を取得する
$context['dataArr'] = $dataArr; 

$context['isUserLogin'] = $ses->isUserLogin();
$template = $twig->loadTemplate('cart.html.twig');
$template->display($context);