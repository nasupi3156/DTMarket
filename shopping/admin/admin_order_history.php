<?php

namespace shopping;

require_once dirname(__FILE__) . '/../Bootstrap.class.php';

use shopping\lib\PDODatabase;
use shopping\lib\Cart;
use shopping\lib\Session;
use shopping\lib\Admin;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$ses = new Session($db);
$cart = new Cart($db);
$admin = new Admin($db);

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
'cache' => Bootstrap::CACHE_DIR
]);

$ses->checkSession();

$order_id = (isset($_GET['order_id']) === true && preg_match('/^\d+$/', $_GET['order_id']) === 1) ? $_GET['order_id'] : '';


if ($order_id !== '') {
   $admin->delAdminOrderList($order_id);
}


$adminOrder = $admin->getAdminOrderList();

$context = [
  'adminOrder' => $adminOrder,

];

$template = $twig->loadTemplate('admin_order_history.html.twig');
$template->display($context);