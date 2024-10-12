<?php

namespace shopping\lib;

class Admin 
{
  private $db = null;

  public function __construct($db)
  {
    $this->db = $db;
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

  public function getAdminSignup()
  {
    try {
        $table = 'users';
        $column = 'user_id, family_name, first_name, family_name_kana, first_name_kana, sex, year, month, day, zip1, zip2, address, email, tel1, tel2, tel3, is_logged_in';
        $where = 'is_deleted = ?';
        $arrVal = [0];
        return $this->db->select($table, $column, $where, $arrVal);
     } catch(\Exception $e) {
        error_log('getAdminSignupでエラーが発生しました : ' . $e->getMessage());
        throw new \Exception('管理者新規情報登録リストの取得に失敗しました。');
     } 
  }

  public function delAdminSignup($user_id)
  {
    try {
      $table = 'users';
      $insData = ['is_deleted' => 1];
      $where = 'user_id = ?';
      $arrVal = [$user_id];
      return $this->db->update($table, $insData, $where, $arrVal); 
    } catch(\Exception $e) {
      error_log('delAdminSignupでエラーが発生しました : ' . $e->getMessage());
      throw new \Exception('管理者新規情報登録リストの削除に失敗しました。');
    }
  } 
  
  public function getAdminOrderList()
  {
    try {
        $table = 'orders o INNER JOIN order_details od ON o.order_id = od.order_id LEFT JOIN users u ON o.user_id = u.user_id';
        $column = 'o.order_id, o.user_id, o.shipping_fee, o.total_price, o.payment_method, o.purchase_date, od.item_id, od.item_name, od.image, od.quantity, od.price';
        $where = 'od.is_deleted = ?';
        $arrVal = [0];
        return $this->db->select($table, $column, $where, $arrVal);
     } catch(\Exception $e) {
        error_log('getAdminOrderListでエラーが発生しました : ' . $e->getMessage());
        throw new \Exception('管理者注文リストの取得に失敗しました。');
     }
  }

  public function delAdminOrderList($order_id)
  { 
    try {
      $table = 'orders';
      $insData = ['is_deleted' => 1];
      $where = 'order_id = ?'; //削除対象のカートID
      $arrVal = [$order_id]; 
      $this->db->update($table, $insData, $where, $arrVal);

      $orderDetailsTable = 'order_details';
      return $this->db->update($orderDetailsTable, $insData, $where, $arrVal);
    } catch(\Exception $e) {
      error_log('delAdminOrderListでエラーが発生しました : ' . $e->getMessage());
      throw new \Exception('管理者注文リストでエラーが発生しました。');
    }
  }  

  public function getAdminContact()
  {
    try {
      $table = 'contacts';
      $column = 'id, family_name, first_name,  email, contents, created_at';
      $where = 'is_deleted = ?';
      $arrVal = [0];
      return $this->db->select($table, $column, $where, $arrVal); 
      } catch(\Exception $e) {
        error_log('getAdminContactでエラーが発生しました : ' . $e->getMessage());
        throw new \Exception('管理者お問い合わせリストの取得に失敗しました。');
      }
  }

  public function delAdminContact($id)
  {
    try {
      $table = 'contacts';
      $insData = ['is_deleted' => 1];
      $where = 'id = ?';
      $arrVal = [$id];
      return $this->db->update($table, $insData, $where, $arrVal);
    } catch (\Exception $e) {
      error_log('delAdminContactでエラーが発生しました : ' . $e->getMessage());
      throw new \Exception('管理者お問い合わせで削除に失敗しました。');
    }
  }

}