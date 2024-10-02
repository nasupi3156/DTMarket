<?php

namespace shopping;

require_once dirname(__FILE__) .'/../Bootstrap.class.php';

use shopping\lib\PDODatabase;
use shopping\lib\Session;
use shopping\lib\Error;
use shopping\lib\Initial;
use shopping\lib\User;

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


// セッションからフォームデータを取得
if (isset($_SESSION['formData'])) {
  $formData = $_SESSION['formData'];
} else {
  header('Location '. Bootstrap::ENTRY_URL . 'auth/regist.php');
  exit();
}


  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete'])) {
    $dataArr = $_SESSION['formData'];

    // セッションからパスワードを取得(usersとuser_passwordsを分けた)
    $password = $dataArr['password'];
    
    try {
      // トランザクション開始
      $db->beginTransaction(); 
    
      $resultUser = $user->insertUser($dataArr);
    
      if ($resultUser) {
      
        // 新規登録が成功したユーザーIDを取得
        $userId = $db->getLastId();

        $resultPass = $user->insertUserPassword($userId, $password); 

        if ($resultUser && $resultPass) {
          // トランザクション中に行われたすべての変更をデータベースに保存
          $db->commit(); 

          $_SESSION['user_id'] = $userId;
            
          $_SESSION['family_name'] = $_SESSION['formData']['family_name'];
          unset($_SESSION['formData']);
          header('Location: ' . Bootstrap::ENTRY_URL . 'auth/complete.php');
          exit();
      } else {
        throw new \Exception('パスワードの挿入が失敗しました。');
      }
    } else {
      throw new \Exception('ユーザー情報の挿入が失敗しました。');
    }
  } catch (\Exception $e) {
    // エラーが発生した場合、トランザクションをロールバックし、トランザクション中に行われたすべての変更を元に戻す。(catchの中)
    $db->rollBack();
    error_log($e->getMessage()); //エラーログ記録
    header('Location:' . Bootstrap::ENTRY_URL . 'confirm.php?error=1');
    exit();
  }
}

list($yearArr, $monthArr, $dayArr) = Initial::getDate();

$sexArr = Initial::getSex();

$context = [
  'yearArr' => $yearArr,
  'monthArr' => $monthArr,
  'dayArr' => $dayArr,
  'sexArr' => $sexArr, 
  'formData' => $formData,
  'error_message' => isset($_GET['error']) && $_GET['error'] == 1 ? '登録中にエラーが発生しました。もう一度お試しください。' : ''
];

$template = $twig->loadTemplate('confirm.html.twig');
$template->display($context);











