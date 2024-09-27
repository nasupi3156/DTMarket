<?php

// Model
namespace shopping\lib;

class Session
{
  // 初期化、空の箱
  public $session_key = ''; 
  public $db = NULL; 
  public $user = NULL;
 
   
  public function __construct($db)
  {  
    session_start();
    // セッションをスタートする
    $this->session_key = session_id();
    // セッションIDを取得する
    $this->db = $db;
    // PDOでdbを使用設定
    $this->user = new User($db);
  }
 
  public function checkSession()
  {
    // セッションIDがある(過去にショッピングカートに来たことがある)
    $customer_no = $this->selectSession();
    if ($customer_no !== false) {
      // ユーザーのセッション情報を保持
      $_SESSION['customer_no'] = $customer_no;
    } else {
     // セッションIDがない(初めてこのサイトに来ている)
     $res = $this->insertSession();
    if ($res === true) {
      $_SESSION['customer_no'] = $this->db->getLastId();
      // getLastID関数 : データベースに最後にinsertされたIDが入る、insertされた後この関数を使えばcustomer_noに簡単にアクセスできて便利
      $_SESSION['family_name'] = '';
      // 空は初期値で真っ白な箱、ログインしてない状態でもエラーを防ぐ
    } else {
      $_SESSION['customer_no'] = '';
      $_SESSION['family_name'] = '';
    }
  }
}

  private function selectSession()
  {
    $table = 'sessions';
    $col = 'customer_no';
    $where = ' session_key = ? ';
    $arrVal = [$this->session_key];
   
    $res = $this->db->select($table, $col, $where, $arrVal); 
    return (count($res) !== 0) ? $res[0]['customer_no'] : false;
  }

  private function insertSession()
  {
    $table = 'sessions';
    $insData = ['session_key' => $this->session_key];
    $res = $this->db->insert($table, $insData);
    return $res;
  }  

  public function isUserLogin() 
  {
    if (isset($_SESSION) && isset($_SESSION['family_name']) && isset($_SESSION['user_id'])
    && !empty($_SESSION['family_name']) && !empty($_SESSION['user_id']) 
    ){
      return true;
    }
      return false;
  }

  public function logout() {
    $user_id = $_SESSION['user_id'] ?? null;
    if ($user_id !== null) {
        $this->user->updateLoginStatus($user_id, 0);
    } else {
        error_log("ログアウト時にユーザーIDが見つかりませんでした。");
    }
    
    unset($_SESSION['customer_no']);
    // 必要に応じて、特定のセッション変数をアンセット
    unset($_SESSION['family_name']);
    unset($_SESSION['user_id']);
    // セッション変数を破棄
    session_unset();
    // セッションを完全に破棄
    session_destroy();
  } 


  // // session_keyをupdate
  // public function updateSessionKey($customer_no, $newSessionId)
  // {
  //   $sql = "UPDATE session SET session_key = :newSessionId WHERE customer_no = :customerNo";
    
  //   $stmt = $this->db->prepare($sql);

  //   $stmt->bindParam(':newSessionId', $newSessionId, \PDO::PARAM_STR);
  //   $stmt->bindParam(':customer_no', $customer_no, \PDO::PARAM_INT);
  //   $stmt->execute();
  // }

}



