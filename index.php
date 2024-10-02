<?php
namespace shopping;


require_once dirname(__FILE__) . '/shopping/Bootstrap.class.php';

use shopping\Bootstrap;


$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
'cache' => Bootstrap::CACHE_DIR,
]);


header("Location: ../shopping/order/list.php");

exit();

$context = [];
$template = $twig->loadTemplate('index.html.twig');
$template->display($context);