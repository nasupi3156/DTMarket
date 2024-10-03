<?php
namespace shopping;


require_once dirname(__FILE__) . '/shopping/Bootstrap.class.php';

use shopping\Bootstrap;


$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
'cache' => Bootstrap::CACHE_DIR,
]);

/** dockerを使う場合 */
// header("Location: ../shopping/order/list.php");

/** ローカルを使う場合 */
header('Location:' . Bootstrap::ENTRY_URL . 'order/list.php');

exit();

$context = [];
$template = $twig->loadTemplate('index.html.twig');
$template->display($context);