<?php

namespace shopping;

require_once dirname(__FILE__) .'/Bootstrap.class.php';

use DateTime;
use shopping\lib\PDODatabase;
use shopping\lib\Session;
use shopping\lib\User;
use shopping\lib\Error;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$ses = new Session($db);
$user = new User($db);
$error = new Error();

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
'cache' => Bootstrap::CACHE_DIR
]);

$errors = [
  'new_password' => [],
  'password_confirm' => [],
  'password_token' => []
];

// tokenがURLパラメーターに含まれてなければlogin
if (!isset($_GET['token'])) {
 
  header('Location:' . Bootstrap::ENTRY_URL . 'login.php');
  exit();
}


  // クエリからトークンを取得
  $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_FULL_SPECIAL_CHARS); 
  // トークンを検索し一致しているユーザ取得
  $passwordResetUser = $user->selectToken($token);
 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'], $_POST['new_password'], $_POST['password_confirm'], $_POST['pass_reset'])) {

  $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_FULL_SPECIAL_CHARS); 
  // トークンをサニタイズし、新しいパスワードと確認パスワードをフォームから取得。
  $newPassword = $_POST['new_password'];
  $passwordConfirm = $_POST['password_confirm'];

  // 再度トークンを検索し一致しているユーザ取得
  $passwordResetUser = $user->selectToken($token);
  
  if (!$passwordResetUser || new DateTime($passwordResetUser['expires_at']) < new DateTime()) {
      $errors['password_token'][] = 'トークンの有効期限が切れています。';

    } elseif (strlen($newPassword) < 8) {
        $errors['new_password'][] = 'パスワードを8文字以上で入力してください。';

      } elseif ($newPassword !== $passwordConfirm) {
          $errors['password_confirm'][] = 'パスワードが一致しませんでした。';

        } else {
          $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
          // ユーザーのemailを使ってパスワードを更新
          $updateSuccess = $user->updatePassword($passwordResetUser['email'], $hashedPassword);
        
          if ($updateSuccess) {
            $user->deleteToken($token);
            header('Location:' . Bootstrap::ENTRY_URL . 'reset_complete.php');
            exit();
          } else {
            $errors['new_password'][] = 'パスワードの更新に失敗しました。';       
        }  
      }
    } 

  $context = [
    'token' => $token,
    'errors' => $errors,
  ];


  $template = $twig->loadTemplate('password_reset.html.twig');
  $template->display($context);

