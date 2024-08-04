<?php
// model
namespace shopping\lib;

class Item
{
  public $cateArr = [];
  public $db = null;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function getCategoryList()
  {
    $table = ' categories ';
    $col = ' ctg_id, category_name ';
    $res = $this->db->select($table, $col);
    return $res;
  }
  
  // 商品,ソート、ページネーション
  public function getItemList($ctg_id, $orderBy, $offset, $itemsPerPage)
  { 
    // カテゴリーによって表示させるアイテムを変える
    $table = ' items i JOIN categories c ON i.ctg_id = c.ctg_id';
    $col = ' i.item_id, i.item_name, i.price,image, i.ctg_id, c.category_name ';
    $where = ($ctg_id !== '') ? '  i.ctg_id = ? ' : '';
    $arrVal = ($ctg_id !== '') ? [$ctg_id] : [];
    
    $limit = "$offset, $itemsPerPage";
    // offset : 行を取得するための開始点を計算 itemsPerPage : 1ページ当たりアイテム数
    $res = $this->db->select($table, $col, $where, $arrVal, $orderBy, $limit);
    return ($res !== false && count($res) !== 0) ? $res : false;  
  }

  

  // 全てのカテゴリーと特定のカテゴリIDに基づいてアイテムの総数をカウント
  // ctg_idの引数をデフォルト値で空にすることで全てとカテゴリーの両方に対応
  public function getTotalItemCount($ctg_id = '') {
    $table = 'items';
    $col = 'COUNT(*) as total_count';
    $where = ($ctg_id !== '') ? 'ctg_id = ?' : '';
    $arrVal = ($ctg_id !== '') ? [$ctg_id] : [];

    $res = $this->db->select($table, $col, $where, $arrVal);
    return ($res !== false) ? $res[0]['total_count'] : 0;
  }

  public function getCategoryDetail()
  {
    $categoriesList = [];
    // categoriesでは全アイテム数を持つ「全て」カテゴリを取得
    $categoriesList = [
      [ 
      // ctgが空なのは特定の商品に限定されずに全てを表示するため
      'ctg_id' => '',
      'category_name' => '全て',
      // 全てのitemの数
      'total_count' => $this->getTotalItemCount()
      ]
    ];
    // cateListでは個別のカテゴリーを取得
    $categoryList = $this->getCategoryList(); 
    
    foreach ($categoryList as $key => $category) {
      // categoryの配列ctg_idはパラメータで受け取り、カテゴリーごとにアイテムを取得
      $ctg_id = $category['ctg_id'];
      // totalメソッドからctg_idでアイテム情報を取得、メソッドからcategoryにtotal_count追加
      $category['total_count'] = $this->getTotalItemCount($ctg_id);
      $categoriesList[] = $category; 
      
    }
    return $categoriesList;
  }


  // 商品の詳細情報を取得する
  public function getItemDetailData($item_id)
  {
    $table = ' items ';
    $col = ' item_id, item_name, detail, price, image, ctg_id ';
    $where = ($item_id !== '') ? ' item_id = ? ' : ''; 
    // カテゴリーによって表示させるアイテムをかえる
    $arrVal = ($item_id !== '') ? [$item_id] : [];

    
    $res = $this->db->select($table, $col, $where, $arrVal);
    return ($res !== false && count($res) !== 0) ? $res : false;
  }

   public function searchItems($query, $ctg_id, $orderBy, $offset = null, $itemsPerPage = null)
   {
     $table = 'items i JOIN categories c ON i.ctg_id = c.ctg_id';
     $column = 'i.item_id, i.item_name, i.price, i.image, i.ctg_id, c.category_name';
     $where = 'i.item_name LIKE ?';
     // queryの所に[mikan]が入り、それがLINK条件に入る
     $arrVal = ['%' . $query . '%'];
 
     if ($ctg_id !== '') {
      // .= は文字列結合演算子、右辺の文字列を左辺の文字列に追加
       $where .= ' AND i.ctg_id = ?';
       $arrVal[] = $ctg_id;
     }

    //  三項演算子の場合
    //  $ctg_id !== '' ? ($where .= ' AND ctg_id = ?') && $arrVal[] = $ctg_id : '';
    
    if ($offset !== null && $itemsPerPage !== null) {
  
      $limit = "$offset, $itemsPerPage";
      $queryResult = $this->db->select($table, $column, $where, $arrVal, $orderBy, $limit);
  } else {
      $column = 'COUNT(*) as total';
      $queryResult = $this->db->select($table, $column, $where, $arrVal, $orderBy);
      return $queryResult !== false ? $queryResult[0]['total'] : 0;
  }

  return $queryResult;

   }

 
  public function  getSearchResultCount($query, $ctg_id, $offset, $itemsPerPage)
  {
    $table = 'items';
    $column = 'item_id, item_name, price, image, ctg_id';
    $where = 'item_name LIKE ?';
    $arrVal = ['%' . $query . '%'];

    // 空ではない場合、カテゴリIDのWHEREの条件追加
    if ($ctg_id !== '') {
        $where .= ' AND ctg_id = ?';
        $arrVal[] = $ctg_id;
    }

    $limit = "$offset, $itemsPerPage";
    return $this->db->select($table, $column, $where, $arrVal, '', $limit);
}


}