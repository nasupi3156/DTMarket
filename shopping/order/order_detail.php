<?php

namespace shopping;

require_once dirname(__FILE__) . '/../Bootstrap.class.php';

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
$loggedIn = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';

$page = isset($_GET['page']) && preg_match('/^[0-9]+$/', $_GET['page']) ? (int)$_GET['page'] : 1;

function calculateShippingFee($sumPrice) {
  return ($sumPrice >= 2000) ? 0 : 300;
}

// 合計注文数を取得
$totalOrders = $cart->getTotalOrderCount($user_id);

// 1ページあたりのアイテム数
$orderPerPage = 20;

// 合計ページ数を計算、合計ページ数が1未満にならないように
$totalPages = max(1, ceil($totalOrders / $orderPerPage));

// ページが合計ページ数を超えないように、pageもtotalPagesも数が小さい番号を優先、存在しないページアクセスを防ぐ
$page = min($page, $totalPages); 


$offset = ($page - 1) * $orderPerPage;
// 行を取得するための開始点を計算 
// 1ページ目、$offset = (1 - 1) * 20 = 0 : 0~19


// 現在のページの注文を取得
$orders = $cart->getOrders($user_id, $offset, $orderPerPage);

//  注文ごとに関連するデータをグループ化して、初期化
$groupOrderHistory = [];

foreach ($orders as $order) {
  $orderId = $order['order_id']; 
  // 各注文のアイテムを取得
  $orderItems = $cart->getOrderItems($orderId);
   // 注文ごとにユーザー情報をグループ化
  $groupOrderHistory[$orderId] = [
    'order_id' => $orderId, 
    'purchase_date' => $order['purchase_date'],
    'family_name' => $order['family_name'],
    'first_name' => $order['first_name'],
    'zip1' => $order['zip1'],
    'zip2' => $order['zip2'],
    'address' => $order['address'],
    'tel1' => $order['tel1'],
    'tel2' => $order['tel2'],
    'tel3' => $order['tel3'],
    'shipping_fee' => $order['shipping_fee'],
    'total_price' => $order['total_price'],
    'payment_method' => $order['payment_method'],
    'items' => $orderItems // アイテム情報をグループ化
  ];
}
   
list($sumNum, $sumPrice) = $cart->getItemAndSumPrice($customer_no);

$shipping_fee = calculateShippingFee($sumPrice);

$context = [
  'family_name' => !empty($_SESSION['family_name']) ? $_SESSION['family_name'] : 'ゲスト',
  'currentPage' => $page,  
   // 現在のページ番号を表す変数
  'totalPages' => $totalPages, 
   // 全体のページ数を表す変数
  'loggedIn' => $loggedIn,
  'shipping_fee' => $shipping_fee,
  'groupOrderHistory' => $groupOrderHistory
];

$context['sumNum'] = $sumNum; 
// 数の合計
$context['sumPrice'] = $sumPrice;
// 金額合計
$context['isUserLogin'] = $ses->isUserLogin();
$template = $twig->loadTemplate('order_detail.html.twig');
$template->display($context);