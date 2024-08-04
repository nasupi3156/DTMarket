<?php

// Model

namespace shopping\lib;

  class PDODatabase 
  {
    private $dbh = null; 
    private $db_host = '';
    private $db_user = '';
    private $db_pass = '';
    private $db_name = '';
    private $db_type = '';
    private $order = '';
    private $limit = '';
    private $offset = '';
    private $groupby = '';
  
    public function __construct($db_host, $db_user, $db_pass, $db_name, $db_type)
    {
      $this->dbh = $this->connectDB($db_host, $db_user, $db_pass, $db_name, $db_type);
      $this->db_host = $db_host;
      $this->db_user = $db_user;
      $this->db_pass = $db_pass;
      $this->db_name = $db_name;
      //SQL関連
      $this->order = '';
      $this->limit = '';
      $this->offset = '';
      $this->groupby = '';
      // データベース接続情報を受け取りconnectDBメソッドを呼び出しデータベースに接続、接続情報はクラスのプロパティーに保存
    }
    // connectDBの中でMYSQLを使う
    private function connectDB($db_host, $db_user, $db_pass, $db_name, $db_type)
    {
      try
      {
        // 接続エラー発生 → PDOExceptionオブジェクトがスローされる → 例外処理をキャッチする
        switch ($db_type) {
          // データベースに接続する処理
          case 'mysql':
            $dsn = 'mysql:host =' . $db_host . ';dbname=' . $db_name;
            $dbh = new \PDO($dsn, $db_user, $db_pass);
            // データベース接続 
            $dbh->query('SET NAMES utf8'); 
            // 文字コード指定
            break;

          case 'pgsql': // 使わない 
            $dsn = 'pgsql:dbname=' . $db_name . ' host=' . $db_host . ' port=5432';
            $dbh = new \PDO($dsn, $db_user, $db_pass);
          break;
        }
        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $dbh;
      } catch (\PDOException $e) {
          var_dump($e->getMessage());
          // getMessage : PDOでもともと用意
          exit();
        }  
          // return $dbh;
         
    }

    public function prepare($sql)
    {
      return $this->dbh->prepare($sql);
    }

    // トランザクション設置
    public function beginTransaction()
    {
      return $this->dbh->beginTransaction();
    }

    public function commit()
    {
      return $this->dbh->commit();
    }

    public function rollBack()
    {
      return $this->dbh->rollBack();
    }

    public function getLastId()
    {
      return $this->dbh->lastInsertId();
    }
    // 最後に取得したid(user_id)情報を取得
    // 外部キーで繋がってる場合などに使う。外部キーのid同士の確認

  
    // 今回は使われていない
    public function setQuery($query = '', $arrVal = [])
    {
      $stmt = $this->dbh->prepare($query);
      $stmt->execute($arrVal);
    }
   
    public function select($table, $column = '', $where = '', $arrVal = [], $orderBy = '', $limit = '', $groupBy = '')
    {
      $sql = $this->getSql('select', $table, $column, $where, $orderBy, $limit, $groupBy);
       
      // SQLクエリとバインドされる値をログに記録
      $this->sqlLogInfo($sql, $arrVal);
      $stmt = $this->dbh->prepare($sql);
      $res = $stmt->execute($arrVal);

      if ($res === false) {
        // エラーがあればエラーを返す処理
        $this->catchError($stmt->errorInfo());
      }

        
      
        // データを保存するために配列を初期化
        $data = [];
        // while : ループ処理、fetchがデータを取得するたびに実行
        // fetch_assoc : 1行ずつ連想配列
        while ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        // array_push : resultの連想配列からdataに変わりdataの多次元配列
        // data配列の中にfetchでとってきた配列(キーと値)の多次元配列がループしていくつかある
        array_push($data, $result);
      }
      return $data;
    }

    public function count($table, $where = '', $arrVal = [])
    {
      $sql = $this->getSql('count', $table, $where);

      $this->sqlLogInfo($sql, $arrVal);
     
      $stmt = $this->dbh->prepare($sql);

      $res = $stmt->execute($arrVal);

      if ($res === false) {
        $this->catchError($stmt->errorInfo());
      }
      
      $result = $stmt->fetch(\PDO::FETCH_ASSOC);
      // データベースがからとってきた配列を数値型にして返す
      return intval($result['NUM']);
      // intval : カウントを整数として返す
    }

    public function setOrder($order = '')
    {
      if ($order !== '') {
        $this->order = ' ORDER BY ' . $order;
      }
    }
    
    public function setLimitOff($limit = '', $offset = '')
    {
      if ($limit !== "") {
        $this->limit = " LIMIT " . $limit;
      }
      if ($offset !== "") {
        $this->offset = " OFFSET " . $offset;
      }
    }

    public function setGroupBy($groupby)
    {
      if ($groupby !== "") {
        $this->groupby = ' GROUP BY ' . $groupby;
      }
    }

    private function getSql($type, $table, $column = '', $where = '', $orderBy = '', $limit = '', $groupBy = '')
    {
      switch ($type) {
        case 'select':
          $columnKey = ($column !== '') ? $column : "*";
          // 三項演算子
        break;        
   
        case 'count':
          $columnKey = 'COUNT(*) AS NUM ';
          // 全ての行数の値をASのNUMで取得、NUMの値は使用できる
        break;
          default: 
          //switch終了したら全体処理から抜ける
        break;
      }

      $whereSQL = ($where !== '') ? ' WHERE ' . $where : '';
     
      $orderSQL = ($orderBy !== '') ? ' ORDER BY ' . $orderBy : '';

      $limitSQL = ($limit !== '') ? ' LIMIT ' . $limit : '';

      $other = $this->groupby . "  " . $this->order . "  " . $this->limit . "  " . $this->offset;
      // $other : 今回は使ってないので省略
        
      $sql = " SELECT " . $columnKey . " FROM " . $table . $whereSQL . $orderSQL . $limitSQL . $other; 
      return $sql;
    }

 
    public function insert($table, $insData = [])
    {
      $insDataKey = [];
      $insDataVal = []; 
      $preCnt = [];

      $columns = '';
      $preSt = '';
   
      foreach ($insData as $col => $val)
      {
        $insDataKey[] = $col; // [$customer_no, $item_id]
        $insDataVal[] = $val; // [$customer_no, $item_id]
        $preCnt[] = '?';  // [?,?]
      }

      $columns = implode(",", $insDataKey); 
      // 配列を文字列 : 'customer_no, item_id' 
      $preSt = implode(",", $preCnt); 
    
      $sql = " INSERT INTO "
        . $table. " ("        
        . $columns. ") VALUES ("  
        . $preSt                  
        . ") ";

        // INSERT INTO cart (customer_no, item_id) VALUES (?,?)
        // INSERT INTO cart (customer_no, item_id) VALUES (123, 456)

        $this->sqlLogInfo($sql, $insDataVal);

        $stmt = $this->dbh->prepare($sql); 

        $res = $stmt->execute($insDataVal);
        if ($res === false) {
          $this->catchError($stmt->errorInfo());
        }
        return $res;
    }
    
    public function update($table, $insData = [],  $where = '', $arrWhereVal = [])
    {
      $arrPreSt = [];
      foreach ($insData as $col => $val) {
        $arrPreSt[] = $col . " =? ";
        // delCartDataでdelete_flg ' => 1がinsDataなので$colに入りdelete_flg ' = ?が作られ、配列[]なのでimplodeで文字列に処理
      }

      $preSt = implode(',', $arrPreSt);
      
      
      $sql = " UPDATE "
        . $table
        . " SET "
        . $preSt    
        . " WHERE "
        . $where;    

      $updateData = array_merge(array_values($insData), $arrWhereVal);
      // array_merge : 値を単一の配列に結合するために使用、executeは全てのパラメータを単一の配列で受け取る必要がある
      // array_values : 配列からキーを除外して値だけ取り出す関数
      // 更新データ$insData,更新条件（$arrWhereVal）を結合、一つの配列 $updateData
      $this->sqlLogInfo($sql, $updateData);

      $stmt = $this->dbh->prepare($sql);
      $res = $stmt->execute($updateData);

      if ($res === false) {
        $this->catchError($stmt->errorInfo()); 
      }
      return $res;
    }

    private function catchError($errArr = [])
    {
      $errMsg = (!empty($errArr[2]))? $errArr[2]:""; 
      die("SQLエラーが発生しました。" . $errArr[2]);
    }
  
    private function makeLogFile()
    {
      $logDir = dirname(__DIR__) . "/logs";
      if (!file_exists($logDir)) {
        mkdir($logDir, 0777);
      }
     // file_exists: ディレクトリが存在するかどうかを確認する
     // ディレクトリが存在しない場合、0777の権限で作成する
     // 0777: 全ユーザーに対する読み取り、書き込み、実行権限を設定するLinuxコマンド

      $logPath = $logDir . '/shopping.log';
      if (!file_exists($logPath)) {
        touch($logPath);
      }
      return $logPath;
    }

    private function sqlLogInfo($str, $arrVal = [])
    {
      $logPath = $this->makeLogFile();
      $logData = sprintf("[SQL_LOG:%s]: %s [%s]\n", date('Y-m-d H:i:s'), $str, implode(",", $arrVal));
      error_log($logData, 3, $logPath);
    }


    public function getPostcodeSearch($zip)
    {
      $sql = "SELECT pref, city, town FROM postcodes Where zip = :zip LIMIT 1";

      $stmt = $this->dbh->prepare($sql);
      $stmt->bindParam(':zip', $zip, \PDO::PARAM_STR);
      $stmt->execute();
      $res = $stmt->fetch(\PDO::FETCH_ASSOC);
      return $res;
      // zip1.zip2を結合し、postcode内のzipで検索
    }

    
    public function getUserProfile($user_id) 
    {
      try {
          $user_id = $_SESSION['user_id'];
          $sql = 'SELECT * FROM users WHERE user_id = ?';
          $stmt = $this->dbh->prepare($sql);
          $stmt->execute([$user_id]);
          $user = $stmt->fetch(\PDO::FETCH_ASSOC);

          return $user;
      } catch (\PDOException $e) {
          error_log('データベースエラー' . $e->getMessage());
          throw new \Exception('プロフィール情報の取得中にエラーが発生しました。');
      }
      // sessionのuser_idをプレースフォルダーに入れる
      // 購入確認画面でユーザ情報表示
    }
    

  public function insertContact($dataArr)
  {
    try {
      $sql = "INSERT INTO contacts (user_id, family_name,first_name, email, contents) VALUES (?, ?, ?, ?, ?)";
      $stmt = $this->dbh->prepare($sql);
      $params = array_values($dataArr);
      $stmt->execute($params);
      return true;
    } catch (\PDOException $e) {
      error_log("データベースエラー" . $e->getMessage());
      throw new \Exception("お問い合わせの送信中にエラーが発生しました。");
    }
  } 
  
  // purchase
  public function userPurchaseProfile($params, $user_id)
  {
    try {
        $sql = "UPDATE users SET
                family_name = :family_name,
                first_name = :first_name, 
                zip1 = :zip1, 
                zip2 = :zip2,   
                address = :address, 
                tel1 = :tel1, 
                tel2 = :tel2, 
                tel3 = :tel3
                WHERE user_id = :user_id";
        $stmt = $this->dbh->prepare($sql);
        $params["user_id"] = $user_id;
        $updateEdit = $stmt->execute($params); 
        return $updateEdit;
  } catch (\PDOException $e) {
      error_log("データベースエラー" . $e->getMessage());
      throw new \Exception("購入情報の更新中にエラーが発生しました。");
  }
}
  // profile
  public function updateUserProfile($params, $user_id) 
  {
    try {
    $sql = "UPDATE users SET 
            family_name = :family_name,
            first_name = :first_name,  
            email = :email, 
            year = :year, 
            month = :month, 
            day = :day, 
            zip1 = :zip1, 
            zip2 = :zip2,   
            address = :address, 
            tel1 = :tel1, 
            tel2 = :tel2, 
            tel3 = :tel3,
            updated_at = NOW() 
            WHERE user_id = :user_id";
  
    $stmt = $this->dbh->prepare($sql);
    $params['user_id'] = $user_id;
    $stmt->execute($params);
    return true;
  }  catch (\PDOException $e) {
    error_log("データベースエラー" . $e->getMessage());
    throw new \Exception("プロフィール情報の更新中にエラーが発生しました。");
  }
}
  
}
