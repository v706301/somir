<?php
  require_once("CsvParser.class.php");
  require_once("Item.class.php");
  require_once("misc.inc.php");

  define("FAMILY",0);
  define("GENUS",1);
  define("SPECIES",2);
  define("VARIETY",3);
  define("CULTIVAR",4);
  define("NAME",5);
  define("DESC",6);
  define("QUANTITY",7);
  define("PRICE",8);
  define("COMMENT",9);
  
  class ImportInventory extends CsvParser{
    var $updated;
    var $inserted;
    
    function ImportInventory(){
      parent::__construct(10);
      $this->inserted = 0;
      $this->updated = 0;
    }

    function update($values,$linenum){
      global $dbprefix;
      $arr = array();
      $arr["type"]            = Item::TYPE_SEED;
      $arr["family"]           = cknull($values[FAMILY]);
      $arr["genus"]           = cknull($values[GENUS]);
      $arr["species"]         = cknull($values[SPECIES]);
      $arr["variety"]         = $values[VARIETY];
      $arr["cultivar"]        = $values[CULTIVAR];
      $arr["name"]            = trim($arr["genus"]." ".$arr["species"] . (strlen($arr["variety"]) > 0 ? " v. ".$arr["variety"]:"") . (strlen($arr["cultivar"]) > 0 ? " cv. ".$arr["cultivar"]:""));
      $arr["description"]     = $values[DESC];
      $arr["package_qty"]     = cknull($values[QUANTITY]);
      $arr["price"]           = cknull($values[PRICE]);
      $arr["photo_size"]      = null;
      $arr["size"]            = -1;
      $comment                = cknull($values[4]);
      $arr["feature1"]        = null;
      $arr["available"]        = 1;
      if($comment == "new"){
        $arr["feature1"]        = "on";
      }
      else if($comment == "notavailable"){
        $arr["available"]        = 0;
      }
      $entity = new Item($arr);
      if($entity->check(false))
        $entity->dbInsert();
      else
        trace_r($arr);
    }
  }
?>
