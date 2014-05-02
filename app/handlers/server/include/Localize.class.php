<?php

class Localize {
  private function Localize(){}
  
  public static function numeric($number,$noun){
    if($noun == "наименование"){
      $number = "".abs($number);
      if(strlen($number) > 2)
        $number = substr($number,-2);
      $number = intval($number);
      if($number == 0 || ($number >= 5 && $number <= 19)){
        return "наименований";
      }
      else{
        $number %= 10;
        if($number == 1)
          return "наименование";
        else if($number == 2 || $number == 3 || $number == 4)
          return "наименования";
      }
      return "наименований";
    }
    else if($noun == "позиция"){
      $number = "".abs($number);
      if(strlen($number) > 2)
        $number = substr($number,-2);
      $number = intval($number);
      if($number == 0 || ($number >= 5 && $number <= 19)){
        return "позиций";
      }
      else{
        $number %= 10;
        if($number == 1)
          return "позиция";
        else if($number == 2 || $number == 3 || $number == 4)
          return "позиции";
      }
      return "позиций";
    }
    return null;
  }
  
  private static function month($m){
  	if($m == 1)
      return "Янв";
    else if($m == 2)
      return "Фев";
    else if($m == 3)
      return "Мар";
    else if($m == 4)
      return "Апр";
    else if($m == 5)
      return "Май";
    else if($m == 6)
      return "Июн";
    else if($m == 7)
      return "Июл";
    else if($m == 8)
      return "Авг";
    else if($m == 9)
      return "Сен";
    else if($m == 10)
      return "Окт";
    else if($m == 11)
      return "Ноя";
    else if($m == 12)
      return "Дек";
    else 
      return false;
  }
  
  public static function dbdate($d){
    if(!is_null($d) && strlen($d) > 0){
      $arr = explode(" ",$d);
      $arr1 = explode("-",$arr[0]);
      $y = $arr1[0];
      $m = $arr1[1];
      $d = $arr1[2];
      if(count($arr) > 1){
        $arr1 = explode(":",$arr[1]);
        $h = $arr1[0];
        $i = $arr1[1];
        $s = $arr1[2];
        return sprintf("%s %s %s %s:%s",$d,Localize::month($m),$y,$h,$i);
      }
      else{
        return sprintf("%s %s %s",$d,Localize::month($m),$y);
      }
    }
  }
}
?>