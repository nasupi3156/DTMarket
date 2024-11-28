<?php

namespace shopping\lib;

class Initial
{
  public static function getDate()
  {
    $yearArr = [];
    $monthArr = [];
    $dayArr = [];
    
    $next_year = date('Y') + 1;
    //来年のデータを定義、Y(year)は年数(今年の数字2023年)、に+1で2024年
    
    for($i = 1900; $i < $next_year; $i ++) {
    $year = sprintf("%04d",$i);
    $yearArr[$year] = $year . '年'; 
    //年を作成  未満の場合繰り返し
    //sprintf : (4桁)の数値を文字列にしフォーマット化して値を返す 
    }

    for($i = 1; $i < 13; $i ++) {
    $month = sprintf("%02d", $i);
    $monthArr[$month] = $month . '月'; 
    }

    for($i = 1; $i < 32; $i ++) {
    $day = sprintf("%02d", $i);
    $dayArr[$day] = $day . '日';
    }
    
    return [$yearArr, $monthArr, $dayArr];
  }
  
  public static function getGender()
  {
    $genderArr = [ 0 => '男性', 1 => '女性', 2 => 'その他'];
    return $genderArr;
  }

}