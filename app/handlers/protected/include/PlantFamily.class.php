<?php

  class PlantFamily{
    private static $FAM = array(
    "Cactaceae"
    ,"Agavaceae"
    ,"Asclepiadaceae"
    ,"Crassulaceae"
    ,"Cucurbitaceae"
    ,"Euphorbiaceae"
    ,"Liliaceae"
    ,"Mezembryanthemaceae"
    ,"Portulacaceae"
    ,"n/a"
    );
    
    private function PlantFamily(){}
    
    public static function getList(){
      return PlantFamily::$FAM;
    }
    
    public static function getCount(){return count(PlantFamily::$FAM);}
    public static function get($i){
      if(isset(PlantFamily::$FAM[$i]))
        return PlantFamily::$FAM[$i];
      else
        return null;
    }
    public static function getName($i){
      if(isset(PlantFamily::$FAM[$i])){
        if(PlantFamily::$FAM[$i] == "n/a")
          return "Не указано";
        else
          return PlantFamily::$FAM[$i];
      }
      else
        return null;
    }
  }
?>