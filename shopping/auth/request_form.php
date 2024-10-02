<?php

namespace shopping;

require_once dirname(__FILE__) .'/../Bootstrap.class.php';

use shopping\lib\PDODatabase;
use shopping\lib\Session;
use shopping\lib\Error;
use shopping\lib\User;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$ses = new Session($db);
$user = new User($db);
$error = new Error();

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
'cache' => Bootstrap::CACHE_DIR
]);

$errArr = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['click'])) {
  $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errArr['invalid_email'] = '正しいメールアドレスを入力してください。';
  } else {
    // 安全なランダムトークンを生成
  $token = bin2hex(random_bytes(16));
  // トークンの有効期限を1時間
  $expires_at = time() + 3600;

  $emailUser = $user->emailConfirm($email);
  if ($emailUser) {
    $user_id = $emailUser['user_id'];
    $user->insertPasswordReset($user_id, $email, $token, $expires_at);
      
  mb_language("Japanese");
  mb_internal_encoding("UTF-8");
  
  // 変数から送信者のメールアドレスを取得
  $fromEmail = getenv('SENDING_EMAIL');

  // メールヘッダを設定
  $header = "From : $fromEmail\n";
  $header .= "Content-Type : text/plain";
  
    
  // 受信者のメールアドレス
  $to = $email; 
  
  $url = "http://localhost/DTMarket/shopping/auth/password_reset.php?token=$token";
  
  $subject = 'パスワードリセット用URLをお送りします';
  
  // メール本文を設定、複数行の文字列にURLを含む(この例では2つ改行)
  $body = 
  '1時間以内に下記のURLへアクセスし、パスワードの変更を完了してください' . "\r\n\r\n" . $url . "\r\n\r\n" . 'このメールは送信用です。';
  
  // メール送信
  $response = mb_send_mail($to, $subject, $body, $header);

  if ($response) {
    header('Location:' . Bootstrap::ENTRY_URL . 'auth/request_success.php');
    exit();

    } else {
      // メールサーバーや設定の問題でパスワードリセットメールの送信に失敗した場合
      $errArr['email_not_found'] = 'メール送信に失敗しました。後ほどもう一度お試しください。';
    }
    
    } else {
      // メールアドレスが違う、ユーザ入力がデータベースにない
      $errArr['email_failed'] = "入力されたメールアドレスは登録されていません。再度確認してもう一度お試しください。";  
    }
  }
}

  $context = [];
  $context['errArr'] = $errArr;
  $template = $twig->loadTemplate('request_form.html.twig');
  $template->display($context);

