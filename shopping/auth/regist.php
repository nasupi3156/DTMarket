<?php

namespace shopping;

require_once dirname(__FILE__) .'/../Bootstrap.class.php';

use shopping\lib\PDODatabase;
use shopping\lib\Session;
use shopping\lib\User;
use shopping\lib\Error;
use shopping\lib\Initial;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db);
$user = new User($db);
$error = new Error();

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
'cache' => Bootstrap::CACHE_DIR
]);

// 初期化
$errArr = []; 
$dataArr = [];
$delete = false;

$duplicateEmail = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
 
  // サニタイズ  ユーザー入力の値取得
  $dataArr = [
    'family_name' => filter_input(INPUT_POST, 'family_name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
    'first_name' => filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'family_name_kana' => filter_input(INPUT_POST, 'family_name_kana', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'first_name_kana' => filter_input(INPUT_POST, 'first_name_kana', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'gender' => filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
    'year' => filter_input(INPUT_POST, 'year', FILTER_SANITIZE_NUMBER_INT) ?? 0, 
    'month' => filter_input(INPUT_POST, 'month', FILTER_SANITIZE_NUMBER_INT) ?? 0,
    'day' => filter_input(INPUT_POST, 'day', FILTER_SANITIZE_NUMBER_INT) ?? 0,
    'zip1' => filter_input(INPUT_POST, 'zip1', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'zip2' => filter_input(INPUT_POST, 'zip2', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'address' => filter_input(INPUT_POST, 'address', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '',
    'tel1' => filter_input(INPUT_POST, 'tel1', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'tel2' => filter_input(INPUT_POST, 'tel2', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'tel3' => filter_input(INPUT_POST, 'tel3', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
    'password' => filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '',
  ];

  // メールアドレスの重複チェック
  $emailCheckResult = $user->emailCheck($dataArr['email']);
    if($emailCheckResult['cnt'] > 0) {
      $duplicateEmail = true;  
    }

  // エラーチェック
  $errArr = $error->errorCheck($dataArr);
  

  $delete = $user->isUserDeleted($dataArr['email']);
   if ($delete) {
    $errArr['email'] = "このメールアドレスは退会済みのため使用することができません";
  } 

  
  if ($error->getErrorFlg() === true && !$duplicateEmail && !$delete) {
  // エラーがなく、メールアドレスの重複もなく、退会もなく
  
  $_SESSION['formData'] = $dataArr; 
  
  header('Location: ' . Bootstrap::ENTRY_URL . 'auth/confirm.php');
    exit();
  } else {
    $context['error_message'] = "申し訳ございません。システムエラーが発生しました。もう一度お試しください。";
  } 
}

list($yearArr, $monthArr, $dayArr) = Initial::getDate();

$genderArr = Initial::getGender();

$context = [
  'yearArr' => $yearArr,
  'monthArr' => $monthArr,
  'dayArr' => $dayArr,
  'genderArr' => $genderArr,
  'formData' => $dataArr,
  'errArr' => $errArr,
  'delete' => $delete,
  'duplicateEmail' => $duplicateEmail,
];

$template = $twig->loadTemplate('regist.html.twig');
$template->display($context);


  