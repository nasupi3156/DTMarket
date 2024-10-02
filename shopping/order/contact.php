<?php

namespace shopping;

require_once dirname(__FILE__) .'/../Bootstrap.class.php';

use shopping\Bootstrap;
use shopping\lib\PDODatabase;
use shopping\lib\Cart;
use shopping\lib\Error;
use shopping\lib\Session;

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap :: TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
'cache' => Bootstrap :: CACHE_DIR
]);

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db);
$cart = new Cart($db);
$error = new Error();

$ses->checkSession();
$customer_no = isset($_SESSION['customer_no']) ? ($_SESSION['customer_no']) : null;
$user_id = isset($_SESSION['user_id']) ? ($_SESSION['user_id']) : null;



$dataArr = [];
$errArr = [];
$duplicateEmail = false;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $dataArr = [
  'user_id' => $user_id,
  'family_name' => filter_input(INPUT_POST, 'family_name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
  'first_name' => filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
  'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '',
  'contents' => filter_input(INPUT_POST, 'contents', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
  ];

  $errArr = $error->contactErrorCheck($dataArr);
  
  if ($error->getErrorFlg() === true) {
   
    $result = $db->insertContact($dataArr);
   
    if($result) {
      header('Location:'. Bootstrap::ENTRY_URL . 'order/contact_thanks.php');
      exit();
    } else {
      header('Location:'. Bootstrap::ENTRY_URL . 'order/contact.php');
    }
  } 
}

list($sumNum, $sumPrice) = $cart->getItemAndSumPrice($customer_no);


$context = [
  'family_name' => !empty($_SESSION['family_name']) ? $_SESSION['family_name'] : 'ゲスト',
  'sumNum' => $sumNum,
];
$context['dataArr'] = $dataArr;
$context['errArr'] = $errArr;
$context['duplicateEmail'] = $duplicateEmail ;
$context['isUserLogin'] = $ses->isUserLogin();

$template = $twig->loadTemplate('contact.html.twig');
$template->display($context);