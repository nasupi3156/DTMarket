<?php
 
namespace shopping;

date_default_timezone_set('Asia/Tokyo');

require_once dirname(__FILE__) . '/../vendor/autoload.php';

class Bootstrap
{
  /** docker環境を使う場合 */
  // const DB_HOST = 'db'; 
  // const APP_DIR = '/var/www/html/'; 
  // const APP_URL = 'http://localhost:8888/';
  

  /** local環境を使う場合 */
  const DB_HOST = 'localhost'; 
  const APP_DIR = '/Applications/MAMP/htdocs/DTMarket/';
  const APP_URL = 'http://localhost/DTMarket/';


  const DB_NAME = 'DTshopping_db';
  const DB_USER = 'DTshopping_user';
  const DB_PASS = 'DTshopping_pass';
  const DB_TYPE = 'mysql';
  

  const TEMPLATE_DIR = self::APP_DIR . 'templates/shopping/';
  const CACHE_DIR = false;
  const ENTRY_URL = self::APP_URL . 'shopping/';
  // const CACHE_DIR = self::APP_DIR . 'templates_c/shopping/';

  public static function loadClass($class)
  {
    $path = str_replace('\\', '/', self::APP_DIR . $class . '.class.php');
    
    require_once $path;
  }
}
//これを実行しないとオートローダーとして動かない
  spl_autoload_register([
    'shopping\Bootstrap', 'loadClass'
  ]);