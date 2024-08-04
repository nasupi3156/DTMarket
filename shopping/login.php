<?php

namespace shopping;

require_once dirname(__FILE__) .'/Bootstrap.class.php';

use shopping\lib\PDODatabase;
use shopping\lib\Error;
use shopping\lib\User;
use shopping\lib\Session;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$ses = new Session($db);
$user = new User($db);
$error = new Error();

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
'cache' => Bootstrap::CACHE_DIR
]);

$ses->checkSession();

if (isset($_GET['logout']) && $_GET['logout'] = '1') {
  $ses->logout(); 
  header ('Location: ' . Bootstrap::ENTRY_URL . 'login.php');
  exit();
}

// 初期化、空の箱
$dataArr = [];
$errArr = [];

// 退会済みかどうか、false初期化
$delete = false; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  $password = filter_input(INPUT_POST, 'password', FILTER_DEFAULT);
  $dataArr = [
  'email' => $email,
  'password' => $password,
  ];

  // エラーチェック
  $errArr = $error->loginErrorCheck($dataArr); 
 
  if ($error->getErrorFlg() === true) {
  // エラーがなければ
    
    $delete = $user->isUserDeleted($email);

    if ($delete) {
      $errArr['email'] = "このメールアドレスは退会済みのためログインできません。再登録してください。";
    } else {
     
    if ($user->loginUser($dataArr['email'], $dataArr['password'])) {
      
      //  // 新しいセッションIDを取得
      //  $newSessionId = session_id();
  
      // //  データベースのセッションID(session_key)を更新
      //  $db->updateSessionKey($customer_no, $newSessionId);
       $user_id = $_SESSION['user_id'];

       $is_admin = $_SESSION['is_admin'] ?? false;
       
      if ($_SESSION['is_admin']) {
        
        unset($_SESSION['family_name']);
        unset($_SESSION['user_id']);

        header('Location:' . Bootstrap::ENTRY_URL . 'admin/admin_login.php');
        exit();
      } else {
        unset($_SESSION['is_admin']);
        $user_id = $_SESSION['user_id'];
        $user->updateLoginStatus($user_id, 1);

        header('Location: ' . Bootstrap::ENTRY_URL . 'list.php');
        exit();
      }
      } else {
        $errArr['login'] = "メールアドレスまたはパスワードが正しくありません";
      }
    }
  }
}

$context = [];
$context['dataArr'] = $dataArr;
$context['errArr'] = $errArr;
$context['delete'] = $delete;

$template = $twig->loadTemplate('login.html.twig');
$template->display($context);



