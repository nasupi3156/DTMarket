<?php

namespace shopping\lib;

class User {

  private $db = null;
  

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function insertUser($dataArr)
  {
    try {
        $sql = "INSERT INTO users (family_name, first_name, family_name_kana, first_name_kana, sex, year, month, day, email, zip1, zip2, address, tel1, tel2, tel3, registed_at) 
                VALUES (:family_name, :first_name, :family_name_kana, :first_name_kana, :sex, :year, :month, :day, :email, :zip1, :zip2, :address, :tel1, :tel2, :tel3, :registed_at)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':family_name', $dataArr['family_name'], \PDO::PARAM_STR);
        $stmt->bindParam(':first_name', $dataArr['first_name'], \PDO::PARAM_STR);
        $stmt->bindParam(':family_name_kana', $dataArr['family_name_kana'], \PDO::PARAM_STR);
        $stmt->bindParam(':first_name_kana', $dataArr['first_name_kana'], \PDO::PARAM_STR);
        $stmt->bindParam(':sex', $dataArr['sex'], \PDO::PARAM_STR);
        $stmt->bindParam(':year', $dataArr['year'], \PDO::PARAM_INT);
        $stmt->bindParam(':month', $dataArr['month'], \PDO::PARAM_INT);
        $stmt->bindParam(':day', $dataArr['day'], \PDO::PARAM_INT);
        $stmt->bindParam(':email', $dataArr['email'], \PDO::PARAM_STR);
        $stmt->bindParam(':zip1', $dataArr['zip1'], \PDO::PARAM_STR);
        $stmt->bindParam(':zip2', $dataArr['zip2'], \PDO::PARAM_STR);
        $stmt->bindParam(':address', $dataArr['address'], \PDO::PARAM_STR);
        $stmt->bindParam(':tel1', $dataArr['tel1'], \PDO::PARAM_STR);
        $stmt->bindParam(':tel2', $dataArr['tel2'], \PDO::PARAM_STR);
        $stmt->bindParam(':tel3', $dataArr['tel3'], \PDO::PARAM_STR);

        // 変数に代入してから渡す
        $registedAt = date('Y-m-d H:i:s');
        $stmt->bindParam(':registed_at', $registedAt, \PDO::PARAM_STR);
        return $stmt->execute();
    } catch (\PDOException $e) {
        error_log("ユーザー挿入エラー: " . $e->getMessage());
        return false;
   } 
}

public function insertUserPassword($userId, $password)
{
    try {
        $sql = "INSERT INTO user_passwords (user_id, password_hash) VALUES (:user_id, :password_hash)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        
        // 変数に代入してから渡す
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bindParam(':password_hash', $passwordHash, \PDO::PARAM_STR);
        return $stmt->execute();
    } catch (\PDOException $e) {
        error_log("パスワード挿入エラー: " . $e->getMessage());
        return false;
    }
  }



  public function emailCheck($email) 
  {
    $sql = 'SELECT COUNT(*) as cnt FROM users WHERE email=?';
    // count(*)関数 : クエリに一致する行の合計数、count結果をas cntでcntという名前で参照
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(\PDO::FETCH_ASSOC);
  
    return $user;
  }

  
  public function loginUser($email, $password, $is_admin = null)
  {
    try {
      $table = "users u INNER JOIN user_passwords up ON u.user_id = up.user_id";
      $column = "u.user_id, u.family_name, u.is_admin, up.password_hash";
      $where = "u.email = ?";
      $arrVal = [$email];

      if ($is_admin !== null) {
        $where .= " AND u.is_admin = ?";
        $arrVal[] = $is_admin;
      }
      
      $user = $this->db->select($table, $column, $where, $arrVal);

      if ($user && isset($user[0]["password_hash"]) && !empty($user[0]["password_hash"])) {
        if (password_verify($password, $user[0]["password_hash"])) {
        
          $this->userInfo($user[0]);
          return true;
        }
      }
      return false;
    } catch (\PDOException $e) {
      error_log("ログインエラー:" . $e->getMessage());
      return false;
    }
  }

  public function userInfo($data) 
  {
    // 引数を取ることでメソッド内で$dataが使える
    $_SESSION['user_id'] = $data['user_id'];         
    $_SESSION['family_name'] = $data['family_name']; 
    $_SESSION['is_admin'] = $data['is_admin'];  
  }


  public function adminLogin($email, $password, $is_admin = null) 
  {
    try {
        $table = "users u INNER JOIN user_passwords up ON u.user_id = up.user_id";
        $column = "u.email, u.is_admin, up.password_hash";
        $where = "u.email = ?";
        $arrVal = [$email];
        
        if ($is_admin !== null) {
            $where .= " AND u.is_admin = ?";
            $arrVal[] = $is_admin;
        }

        // $this->db->selectがクエリを実行し結果を返すメソッドであることを仮定
        $adminUser = $this->db->select($table, $column, $where, $arrVal);
        
        if ($adminUser && password_verify($password, $adminUser[0]["password_hash"])) {
            return $adminUser;
        } 
        return false;
    } catch (\PDOException $e) {
        error_log("ログインエラー: " . $e->getMessage());
        return false;
    }
}


  // profile
  public function logicalDeleteUser($user_id)
  {
    $sql = "UPDATE users SET is_removed = 1, deleted_at = NOW() WHERE user_id = :user_id";
    // is_removedが1で退会、deleted_atで退会した日時を指定
    
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
    // trueとfalseだけど0と1なのでint(整数)
    $userDelete = $stmt->execute();
    return $userDelete;
  }


  public function isUserDeleted($email)
  {
    $sql = "SELECT is_removed FROM users WHERE email = :email";
    $stmt = $this->db->prepare($sql);
    $stmt -> bindParam(':email', $email, \PDO::PARAM_STR);
    $stmt -> execute();
    // 単一の列の値を取得する、fetchColumn()を使用してis_列の値を取得し、その値が1であればtrueを返す
    return $stmt->fetchColumn() == 1;
  }

  public function updateLoginStatus($user_id, $is_logged_in)
  {
    try {
      $sql = "UPDATE users SET is_logged_in = :is_logged_in WHERE user_id = :user_id";
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(":user_id", $user_id, \PDO::PARAM_INT);
      $stmt->bindParam(":is_logged_in", $is_logged_in, \PDO::PARAM_INT);
      $stmt->execute();
    } catch (\PDOException $e){
      error_log("データベースエラー" . $e->getMessage());
    } 
  } 

  // reset
  public function insertPasswordReset($user_id, $email, $token, $expires_at)
  {
    $sql = "INSERT INTO password_resets (user_id, email, token, expires_at) VALUES (:user_id, :email, :token, :expires_at)";
    // expiresはUNIXタイムスタンプなのでDATETIME形式に変換
    $expires_time = date('Y-m-d H:i:s', $expires_at);
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, \PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, \PDO::PARAM_STR);
    $stmt->bindParam(':token', $token, \PDO::PARAM_STR);
    $stmt->bindParam(':expires_at', $expires_time, \PDO::PARAM_STR);
     // $expires_time : 変換した日時をバインド
    $stmt->execute();
  }

  public function selectToken($token)
  {
    $sql = "SELECT * FROM password_resets WHERE token = :token";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':token', $token, \PDO::PARAM_STR);
    $stmt->execute();
    $passwordResetUser = $stmt->fetch(\PDO::FETCH_ASSOC);
    return  $passwordResetUser;
  }

  // pass-reset
  public function updatePassword($email, $hashedPassword) 
  {
    $user = $this->emailConfirm($email);

    if ($user) {
      $user_id = $user['user_id'];
    } else {
      return false;
    }

    $sql =  "UPDATE user_passwords SET password_hash = :password_hash WHERE user_id = :user_id ";
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':password_hash', $hashedPassword, \PDO::PARAM_STR);
    
    $stmt->bindValue(':user_id', $user_id, \PDO::PARAM_STR);
    
    $updateSuccess = $stmt->execute();
    return $updateSuccess;
  }

  public function emailConfirm($email)
  {
    // LIMIT : 最初の1行だけを取得
    $sql = 'SELECT * FROM users WHERE email = :email LIMIT 1';
    $stmt = $this->db->prepare($sql);
    // :emailはsqlのプレースホルダー何処に値を挿入するかを指定、$emailがプレースホルダーにバインドする値
    // \PDO::PARAM_STRは文字列型を指定  
    $stmt->bindValue(':email', $email, \PDO::PARAM_STR);
    $stmt->execute();
    $emailUser = $stmt->fetch(\PDO::FETCH_ASSOC);
   
    return $emailUser ;
  }

 
  public function deleteToken($token)
  {
    $sql = "DELETE FROM password_resets WHERE token = :token";
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':token', $token, \PDO::PARAM_STR);
    $stmt->execute();
  }

}
  