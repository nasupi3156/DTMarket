<?php

namespace shopping;

require_once dirname(__FILE__). '/Bootstrap.class.php';

use shopping\lib\PDODatabase;
use shopping\Bootstrap;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

if (isset($_GET['zip1']) === true && isset($_GET['zip2']) === true){

  $zip1 = trim($_GET['zip1']);
  $zip2 = trim($_GET['zip2']);
  // trim ： ユーザーが入力した余分な空白などを削除
  
  $res = $db -> getPostcodeSearch($zip1. $zip2); // 郵便番号を結合

  if ($res) {
    echo ($res !== '' && count($res) !== 0) ? $res['pref']. $res['city'].$res['town']: false;
 
  } else {
    echo 'no';
  }
}
