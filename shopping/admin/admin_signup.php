<?php

namespace shopping;

require_once dirname(__FILE__) . '/../Bootstrap.class.php';

use shopping\lib\PDODatabase;
use shopping\lib\Cart;
use shopping\lib\Session;
use shopping\lib\User;
use shopping\lib\Admin;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$ses = new Session($db);
$cart = new Cart($db);
$user = new User($db);
$admin = new Admin($db);


$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
'cache' => Bootstrap::CACHE_DIR
]);

$ses->checkSession();


$user_id = (isset($_GET['user_id']) === true && preg_match('/^\d+$/', $_GET['user_id']) === 1) ? $_GET['user_id'] : '';


if ($user_id !== '') {
   $admin->delAdminSignup($user_id);
}


$adminSignup = $admin->getAdminSignup();

$user->updateLoginStatus($user_id, 1);



$context = [
  'adminSignup' => $adminSignup,
  // 'modalOrder' => $adminOrder[0]
  'user' => $user
];



$template = $twig->loadTemplate('admin_signup.html.twig');
$template->display($context);