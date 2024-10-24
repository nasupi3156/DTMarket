<?php

namespace shopping;

require_once dirname(__FILE__) .'/../Bootstrap.class.php';

use shopping\Bootstrap;
use shopping\lib\PDODatabase;
use shopping\lib\Session;
use shopping\lib\Cart;
use shopping\lib\Item;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$ses = new Session($db);
$cart = new Cart($db);
$itm = new Item($db);

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, ['cache' => Bootstrap::CACHE_DIR]);

$ses->checkSession();

$customer_no = isset($_SESSION['customer_no']) ? $_SESSION['customer_no'] : '';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';
$loggedIn = isset($_SESSION['user_id']);

$item_id = (isset($_GET['item_id']) === true && preg_match('/^\d+$/', $_GET['item_id']) === 1) ? $_GET['item_id'] : '';

$page = isset($_GET['page']) && preg_match('/^[0-9]+$/', $_GET['page']) ? (int)$_GET['page'] : 1;


$orderPerPage = 20;

$offset = ($page - 1) * $orderPerPage;

$totalOrders = $cart->getTotalOrderCount($user_id);

$orderHistory = $cart->getOrderHistoryData($user_id, $offset, $orderPerPage); 



$totalPages = max(1, ceil($totalOrders / $orderPerPage));

$page = min($page, $totalPages); 

// 全体の初期化
$groupOrderHistory = [];
// 注文履歴アイテムを購入日ごとにグループ化
foreach ($orderHistory as $item) {
  $datetime = $item['purchase_date']; 
  if(!isset($groupOrderHistory[$datetime])) {

  $groupOrderHistory[$datetime] = [
    'datetime' => $datetime, // 購入日を格納
    'orders' => [], // 購入日の注文アイテムを保持する空の配列を初期化
    'total_price' => 0, 
  ];
  }
  
  // ordersでアイテム情報取得
  $groupOrderHistory[$datetime]['orders'][] = [
    // []は、アイテム情報の配列を保持
    'item_id' => $item['item_id'],
    'item_name' => $item['item_name'],
    'image' => $item['image'],
    'price' => $item['price'],
    'quantity' => $item['quantity'],
  ];
  $groupOrderHistory[$datetime]['total_price'] += $item['price'] * $item['quantity'];
  // グループの合計価格
}


list($sumNum, $sumPrice) = $cart->getItemAndSumPrice($customer_no);

$context = [
  'family_name' => !empty($_SESSION['family_name']) ? $_SESSION['family_name'] : 'ゲスト',
  'totalPages' => $totalPages,
  'currentPage' => $page,

  'groupOrderHistory' => $groupOrderHistory,
  'sumNum' => $sumNum,
  'loggedIn' => $loggedIn,
];

$context['isUserLogin'] = $ses->isUserLogin();

$template = $twig->loadTemplate('order.html.twig');
$template->display($context);