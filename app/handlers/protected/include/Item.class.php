<?php
  require_once("upload.inc.php");
  class Item{
    const TYPE_SEED = 0;
    const TYPE_PLANT = 1;
    private static $types = array(
      Item::TYPE_SEED => "Семена",
      Item::TYPE_PLANT => "Растение",
    );
    private $id = null;
    private $name = null;
    private $family = null;
    private $genus = null;
    private $species = null;
    private $variety = null;   
    private $cultivar = null;   
    private $package_qty = 1;
    private $price = null; 
    private $description = null;
    private $type = 1; 
    private $size = null;
    private $available = 1;
    private $feature1 = "on";
    private $photo = null;
    private $valid = true;

    function init($row = null){
      if(!is_null($row)){
        if(isset($row["id"]))
          $this->id = intval($row["id"]);
        else
          $this->id = null;
        $this->name         = $row["name"];
        $this->family       = $row["family"];
        $this->genus        = $row["genus"];
        $this->species      = $row["species"];
        $this->variety      = $row["variety"];
        $this->cultivar      = $row["cultivar"];
        $this->description  = $row["description"];
        $this->price        = floatval($row["price"]);
        $this->size        = $row["size"];
        $this->package_qty  = intval($row["package_qty"]);
        $this->type      = intval($row["type"]);
        $this->available    = intval($row["available"]);
        $this->feature1      = $row["feature1"];

        if(intval($row["photo_size"]) > 0){
          $this->photo = array();
          $this->photo["photo_id"] = $row["photo_id"];
          $this->photo["photo_file"] = $row["photo_file"];
          $this->photo["photo_type"] = $row["photo_type"];
          $this->photo["photo_size"] = $row["photo_size"];
          $this->photo["photo_width"] = $row["photo_width"];
          $this->photo["photo_height"] = $row["photo_height"];
          $this->photo["thumb_type"] = $row["thumb_type"];
          $this->photo["thumb_size"] = $row["thumb_size"];
          $this->photo["thumb_width"] = $row["thumb_width"];
          $this->photo["thumb_height"] = $row["thumb_height"];
        }
      }
    }

    function Item($row = null){
      $this->init($row);
    }

    public function isValid(){return $this->valid;}
    public function setValid($b){$this->valid = $b;}

    public function isSeed(){return $this->type == 0;}
    public function setSeed($x){$this->type = intval($x);}

    public function getId(){return $this->id;}
    public function setId($id){$this->id = $id;}

    public function getName(){return $this->name;}
    public function setName($name){$this->name = $name;}

    public function getFamily(){return $this->family;}
    public function setFamily($family){$this->family = $family;}

    public function getGenus(){return $this->genus;}
    public function setGenus($genus){$this->genus = $genus;}

    public function getSpecies(){return $this->species;}
    public function setSpecies($species){$this->species = $species;}

    public function getVariety(){return $this->variety;}
    public function setVariety($variety){$this->variety = $variety;}

    public function getCultivar(){return $this->cultivar;}
    public function setCultivar($cultivar){$this->cultivar = $cultivar;}

    public function getDescription(){return $this->description;}
    public function setDescription($description){$this->description = $description;}

    public function getPrice($currencyCoef = 1){
      return floatval(sprintf("%.2f",$this->price*$currencyCoef));
    }
    public function setPrice($price){$this->price = $price;}

    public function getSize(){return $this->size;}
    public function setSize($size){$this->size = $size;}

    public function getAvailable(){return $this->available;}
    public function setAvailable($available){$this->available = $available;}

    public function getFeature1(){return $this->feature1;}
    public function setFeature1($feature1){$this->feature1 = $feature1;}

    public function getPackageQty(){return $this->package_qty;}
    public function setPackageQty($package_qty){$this->package_qty = $package_qty;}

    public function getPhotoInfo(){return $this->photo;}
    public function hasPhoto(){return $this->photo != null;}
    public function getPhotoId(){return $this->hasPhoto() ? $this->photo["photo_id"] : null;}
    public function getPhotoFile(){return $this->hasPhoto() ? $this->photo["photo_file"] : null;}
    public function getPhotoType(){return $this->hasPhoto() ? $this->photo["photo_type"] : null;}
    public function getPhotoSize(){return $this->hasPhoto() ? $this->photo["photo_size"] : null;}
    public function getPhotoWidth(){return $this->hasPhoto() ? $this->photo["photo_width"] : null;}
    public function getPhotoHeight(){return $this->hasPhoto() ? $this->photo["photo_height"] : null;}
    public function getThumbType(){return $this->hasPhoto() ? $this->photo["thumb_type"] : null;}
    public function getThumbSize(){return $this->hasPhoto() ? $this->photo["thumb_size"] : null;}
    public function getThumbWidth(){return $this->hasPhoto() ? $this->photo["thumb_width"] : null;}
    public function getThumbHeight(){return $this->hasPhoto() ? $this->photo["thumb_height"] : null;}

    public function httpRead(){
      $this->setId((is_null(getParameter("item_id"))) ? null : intval(getParameter("item_id")));
      $this->setName(getParameter("name"));
      $this->setFamily(getParameter("family"));
      $this->setGenus(getParameter("genus"));
      $this->setSpecies(getParameter("species"));
      $this->setVariety(getParameter("variety"));
      $this->setCultivar(getParameter("cultivar"));
      $this->setDescription(getParameter("description"));
      $this->setPrice(floatval(getParameter("price")));
      $this->setSize(getParameter("size"));
      $this->setFeature1(getParameter("feature1"));
      $this->setAvailable(getParameter("available"));
      $package_qty = intval(getParameter("package_qty"));
      $this->setPackageQty($package_qty == 0 ? 1 : $package_qty);
      $this->setSeed(getParameter("seed"));
      $this->httpReadPhoto();
      $this->setValid(true);
      $this->check();
      //trace_r($this);
      return $this;
    }

    public function httpReadPhoto(){
      $max_thumb_width     = intval(getSetting("max_item_thumb_width",ITEM_THUMB_W));
      $max_thumb_height    = intval(getSetting("max_item_thumb_height",ITEM_THUMB_H));
      $max_photo_width     = intval(getSetting("max_photo_width",ITEM_PHOTO_W));
      $max_photo_height    = intval(getSetting("max_photo_height",ITEM_PHOTO_H));
      $error = null;
      if(getUploadedMedia($this->photo,$error,"photo",$max_photo_width,$max_photo_height,$max_thumb_width,$max_thumb_height)){
        return true;
      }
      else{
        setAlert($error);
        return false;
      }
    }

    public function dbRead($id){
      global $dbprefix;
      $i = null;
      $db = getDatabase();
      $query = sprintf(
      "select\n" .
      "i.*\n" .
      ",ip.id as photo_id\n" .
      ",ip.photo_file\n" .
      ",ip.photo_type\n" .
      ",ip.photo_size\n" .
      ",ip.photo_width\n" .
      ",ip.photo_height\n" .
      ",ip.thumb_type\n" .
      ",ip.thumb_size\n" .
      ",ip.thumb_width\n" .
      ",ip.thumb_height\n" .
      "from ".$dbprefix."_item i\n" .
      "left join ".$dbprefix."_photo ip on ip.item_id=i.id\n" .
      "where i.id=%d"
      ,$id
      );
      //trace($query);
      $rs = $db->query($query, __FILE__ . ":" . __LINE__);
      if($rs !== null){
        $row = $db->fetch_assoc($rs);
        if($row){
          $this->init($row);
          $db->free_result($rs);
          return $i;
        }
      }
      throw new Exception("Не удалось обнаружить растение(семена) под номером ".$id);
    }

    public function dbDelete(){
      global $dbprefix;
      if(!is_null($this->id)){
        $this->dbDeletePhoto();
        $db = getDatabase();
        $query = sprintf(
        "delete\n" .
        "from ".$dbprefix."_item\n" .
        "where id=%d"
        ,$this->id);
        $db->query($query, __FILE__ . ":" . __LINE__);
        //trace($query);
        return true;
      }
      else{
        return false;
      }
    }

    public function dbDeletePhoto($photo_id = null){
      global $dbprefix;
      if(!is_null($this->id)){
        $db = getDatabase();
        if(is_null($photo_id))
          $query = sprintf("delete from ".$dbprefix."_photo where item_id=%d",$this->id);
        else
          $query = sprintf("delete from ".$dbprefix."_photo where item_id=%d and id=%d",$this->id,$photo_id);
        $db->query($query, __FILE__ . ":" . __LINE__);
        return true;
      }
      else{
        return false;
      }
    }

    public function dbInsert(){
      global $dbprefix;
      if(!$this->check())
        return false;
      $query = sprintf(
      "insert into ".$dbprefix."_item\n" .
      "(name\n" .
      ",family\n" .
      ",genus\n" .
      ",species\n" .
      ",variety\n" .
      ",cultivar\n" .
      ",description\n" .
      ",price\n" .
      ",size\n" .
      ",package_qty\n" .
      ",type\n" .
      ",feature1\n" .
      ",available\n" .
      ")\n" .
      "values\n" .
      "(%s" .
      ",%s" .
      ",%s" .
      ",%s" .
      ",%s" .
      ",%s" .
      ",%s" .
      ",%.2f" .
      ",%s" .
      ",%d" .
      ",%d" .
      ",%s" .
      ",%d" .
      ")"
      ,sqlsafe($this->name)
      ,sqlsafe($this->family)
      ,sqlsafe($this->genus)
      ,sqlsafe($this->species)
      ,sqlsafe($this->variety)
      ,sqlsafe($this->cultivar)
      ,sqlsafe($this->description)
      ,floatval($this->price)
      ,sqlsafe($this->size)
      ,intval($this->package_qty)
      ,intval($this->type)
      ,sqlsafe($this->feature1)
      ,intval($this->available)
      );
      //trace($query);
      $db = getDatabase();
      $db->query($query, __FILE__ . ":" . __LINE__ );
      $this->id = $db->insert_id();
      $this->dbInsertPhoto();
      return true;
    }

    public function dbUpdate() {
      global $dbprefix;
      if(!$this->check())
        return false;
      $query = sprintf(
      "update ".$dbprefix."_item\n" .
      "set\n" .
      "name=%s\n" .
      ",family=%s\n" .
      ",genus=%s\n" .
      ",species=%s\n" .
      ",variety=%s\n" .
      ",cultivar=%s\n" .
      ",description=%s\n" .
      ",price=%.2f\n" .
      ",size=%s\n" .
      ",package_qty=%d\n" .
      ",type=%d\n" .
      ",feature1=%s\n" .
      ",available=%d\n" .
      "where id=%d"
      ,sqlsafe($this->name)
      ,sqlsafe($this->family)
      ,sqlsafe($this->genus)
      ,sqlsafe($this->species)
      ,sqlsafe($this->variety)
      ,sqlsafe($this->cultivar)
      ,sqlsafe($this->description)
      ,floatval($this->price)
      ,sqlsafe($this->size)
      ,intval($this->package_qty)
      ,intval($this->type)
      ,sqlsafe($this->feature1)
      ,intval($this->available)
      ,intval($this->id)
      );
      //trace($query);
      $db = getDatabase();
      $db->query($query, __FILE__ . ":" . __LINE__ );
      $this->dbInsertPhoto();

      return true; // ??? check update result?...
    }

    public function dbInsertPhoto(){
      global $dbprefix;
      if(!is_null($this->photo)){
        $db = getDatabase();
        //$query = sprintf("delete from ".$dbprefix."_photo where item_id = %d",$this->id);
        //$db->query($query,__FILE__.":".__LINE__);
        $query = sprintf("insert into ".$dbprefix."_photo\n" .
        "(item_id\n" .
        ",date_created\n" .
        ",photo\n" .
        ",photo_file\n" .
        ",photo_type\n" .
        ",photo_size\n" .
        ",photo_width\n" .
        ",photo_height\n" .
        ",thumb\n" .
        ",thumb_type\n" .
        ",thumb_size\n" .
        ",thumb_width\n" .
        ",thumb_height\n" .
        ")\n" .
        "values\n" .
        "(%d\n"  .  //item_id
        ",now()\n" .  //date_created
        ",%s\n" .  //photo
        ",%s\n" .  //photo name
        ",%s\n" .  //photo type
        ",%d\n" .  //photo size
        ",%d\n" .  //photo width
        ",%d\n" .  //photo height
        ",%s\n" .  //thumb
        ",%s\n" .  //thumb type
        ",%d\n" .  //thumb size
        ",%d\n" .  //thumb width
        ",%d\n" .  //thumb height
        ")"
        ,$this->id
        ,(is_null($this->photo) ? "NULL" : "'".mysql_escape_string($this->photo["content"])."'")
        ,(is_null($this->photo) ? "NULL" : sqlsafe($this->photo["name"]))
        ,(is_null($this->photo) ? "NULL" : sqlsafe($this->photo["type"]))
        ,(is_null($this->photo) ? 0 : $this->photo["size"])
        ,(is_null($this->photo) ? 0 : $this->photo["width"])
        ,(is_null($this->photo) ? 0 : $this->photo["height"])
        ,(is_null($this->photo) ? "NULL" : "'".mysql_escape_string($this->photo["thumb_content"])."'")
        ,(is_null($this->photo) ? "NULL" : sqlsafe($this->photo["thumb_type"]))
        ,(is_null($this->photo) ? 0 : $this->photo["thumb_size"])
        ,(is_null($this->photo) ? 0 : $this->photo["thumb_width"])
        ,(is_null($this->photo) ? 0 : $this->photo["thumb_height"])
        );
        $db->query($query,__FILE__.":".__LINE__);
      }
    }

    public function check($bCheckDuplicate = true){
      if(!$this->valid)
        return false;

      if(strlen($this->name) == 0){
        setAlert("Please type name for this item");
        $this->valid = false;
      }
      if(strlen($this->price) == 0 || floatval($this->price) < 0.0){
        setAlert("Please type level 1 price for this item.\r\n(This will be default price when item is added to the client's inventory)");
        $this->valid = false;
      }
      return $this->valid;
    }

    public static function getItemSearchClause($search_item,$showfilter1,$showfilter2,$showfilter3,$showfilter4){
      $clause = "";
      $search_item = arrclean(explode(" ",trim($search_item)));
      if(!is_null($search_item) && count($search_item) > 0){
        for($i = 0; $i < count($search_item); $i++){
          if(strlen($clause)> 0)$clause .= " or ";
          $clause .= sprintf("upper(i.family) like upper('%%%s%%')\n", sqlsafe($search_item[$i],false));
          if(strlen($clause)> 0)$clause .= " or ";
          $clause .= sprintf("upper(i.genus) like upper('%%%s%%')\n", sqlsafe($search_item[$i],false));
          if(strlen($clause)> 0)$clause .= " or ";
          $clause .= sprintf("upper(i.species) like upper('%%%s%%')\n", sqlsafe($search_item[$i],false));
          if(strlen($clause)> 0)$clause .= " or ";
          $clause .= sprintf("upper(i.variety) like upper('%%%s%%')\n", sqlsafe($search_item[$i],false));
          if(strlen($clause)> 0)$clause .= " or ";
          $clause .= sprintf("upper(i.cultivar) like upper('%%%s%%')\n", sqlsafe($search_item[$i],false));
          $clause = "(".$clause.")";
        }
      }
      if($showfilter1 == "plant"){
        if(strlen($clause)> 0)$clause .= " and ";
        $clause .= "type<>0\n";
      }
      else if($showfilter1 == "seed"){
        if(strlen($clause)> 0)$clause .= " and ";
        $clause .= "type=0\n";
      }
      if($showfilter2 == "available"){
        if(strlen($clause)> 0)$clause .= " and ";
        $clause .= "available<>0\n";
      }
      else if($showfilter2 == "notavailable"){
        if(strlen($clause)> 0)$clause .= " and ";
        $clause .= "available=0\n";
      }
      if(strlen($showfilter3) > 0){
        if(strlen($clause)> 0)$clause .= " and ";
        $clause .= "feature1 is not null\n";
      }
      if(strlen($showfilter4) > 0){
        if(strlen($clause)> 0)$clause .= " and ";
        $clause .= sprintf("lower(family)=%s\n",sqlsafe(strtolower($showfilter4)));
      }
      if(strlen(trim($clause))> 0)$clause = " (" . $clause . ")";
      return $clause;
    }

    public static function getSearchItemCount($search_item = null,$showfilter1 = null,$showfilter2 = null,$showfilter3 = null,$showfilter4 = null,$showfilter5 = null){
      global $dbprefix;
      $db = getDatabase();
      if(strlen($showfilter5) > 0)
        $query = "select count(*) from " . $dbprefix . "_item i join ".$dbprefix."_photo ip on ip.item_id=i.id\n";
      else
        $query = "select count(*) from " . $dbprefix . "_item i\n";
      $search_clause = Item::getItemSearchClause($search_item,$showfilter1,$showfilter2,$showfilter3,$showfilter4);
      if(strlen($search_clause)> 0)
        $query .= "where\n" . $search_clause;
      //trace($query);
      $rs = $db->query($query, __FILE__ . ":" . __LINE__);
      $row = $db->fetch_array($rs);
      $total = $row[0];
      $db->free_result($rs);
      return $total;
    }

    public static function searchItems($search_item = null,$showfilter1 = null,$showfilter2 = null,$showfilter3 = null,$showfilter4 = null,$showfilter5 = null){
      global $dbprefix;
      global $items_per_page,$start_row;
      $db = getDatabase();
      $query = sprintf(
      "select\n" .
      "i.*\n" .
      ",ip.id as photo_id\n" .
      ",ip.photo_size\n" .
      ",ip.thumb_width\n" .
      ",ip.thumb_height\n" .
      "from\n" .
      $dbprefix."_item i\n" .
      (strlen($showfilter5) > 0 ? "":"left ")."join ".$dbprefix."_photo ip on ip.item_id=i.id\n"
      );
      $search_clause = Item::getItemSearchClause($search_item,$showfilter1,$showfilter2,$showfilter3,$showfilter4);
      if(strlen($search_clause)> 0)
        $query .= " where " . $search_clause;
      $query .= getSortOrderClause("i.genus,i.species,i.variety,i.cultivar");
      $query .= $db->getLimitClause($items_per_page,$start_row);
      //trace("\r\n<!-- \r\n".$query."\r\n-->\r\n");
      $rs = $db->query($query, __FILE__ . ":" . __LINE__ );
      return $rs;
    }
  
    public static function getItemTypeName($type_id){
      if(isset(Item::$types[$type_id]))
        return Item::$types[$type_id];
      else
        return null;
    }

    public static function getItemCountByType(){
      global $dbprefix;
      $db = getDatabase();
      $query = "select count(type),type from " . $dbprefix . "_item group by type";
      $rs = $db->query($query, __FILE__ . ":" . __LINE__);
      $arr = array();
      while($rs && $row = $db->fetch_row($rs)){
        $arr[$row[1]] = $row[0];
      }
      $keys = array_keys(Item::$types);
      for($i = 0; $i < count($keys); $i++){
        if(!isset($arr[$keys[$i]]))
          $arr[$keys[$i]] = 0;
      }
      $db->free_result($rs);
      return $arr;
    }

    public static function formatSizeOrQuantity($type,$size,$qty){
      if($type == 0){
        if($qty > 0)
          return sprintf("%d %s",$qty,htmlize('шт'));
        else
          return "-";
      }
      else{
        if(strlen($size) > 0){
          if(is_numeric($size))
            return htmlize($size).' '.htmlize("см");
          else
            return htmlize($size);
        }
        else
          return "-";
      }
    }

    public static function formatPrice($price,$currencyCoef,$currencyLabel,$bZero = true){
      $result = $price*$currencyCoef;
      if(floatval($result) <= 0.0 && !$bZero)
      	return '';
      $s = sprintf("%.2f",$result);
      if(substr($s,-3) == '.00')
      	$s = substr($s,0,-3);
      $s .= ' '.htmlize($currencyLabel);
      return $s;
    } 

    public static function formatDescription($name,$description){
      if(strlen($description) > 0)
        return htmlize($name).sprintf("<br /><span style=\"font-style:italic;\">[%s]</span>",htmlize($description));
      else
        return htmlize($name);
    }
    
    public static function formatCurrency($price){
      
    }
  }
?>