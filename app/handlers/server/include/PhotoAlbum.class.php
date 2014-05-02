<?php
  require_once("User.class.php");
  require_once("upload.inc.php");

  class PhotoAlbum{
    private $id = null;
    private $name = null;
    private $description = null;
    private $photo_count = null;
    private $total_size = null;
    private $exposed_count = null;
    private $exposed_size = null;
    private $date_created = null;
    private $date_updated = null;
    private $thumb_type = null;
    private $thumb_size = null;
    private $thumb_width = null;
    private $thumb_height = null;
    private $last_inserted_photo_id = null;
    private $is_hidden = null;
    private $valid = null;
    private static $_photoFields = false;

    public function __construct($row = null){
      $this->init($row);
    }

		public static function photoFields(){
		  global $dbprefix;
		  if(!self::$_photoFields)
      	self::$_photoFields = <<< _FIELDS_
select
p.id
,p.date_created
,p.thumb_type
,p.thumb_size
,p.thumb_width
,p.thumb_height
,p.photo_size
,p.photo_file
,p.photo_name
,p.photo_keywords
,p.photo_desc
,p.price as photo_price
,p.status as photo_status
,p.diameter
,p.notes
,p.is_hidden
,p.photoalbum_id
,pa.name
,pa.description
,pa.photo_count
,pa.total_size
,pa.exposed_count
,pa.exposed_size
,pa.date_created
,pa.date_updated
,pa.is_hidden
from {$dbprefix}_photo p
left join {$dbprefix}_photoalbum pa on pa.id=p.photoalbum_id

_FIELDS_;
			return self::$_photoFields;		  	
		}

    private function init($row = null){
      $this->setValid(true);
      if(!is_null($row)){
        $this->id = $row["id"];
        $this->name = $row["name"];
        $this->description = $row["description"];
        $this->photo_count = $row["photo_count"];
        $this->total_size = $row["total_size"];
        $this->exposed_count = $row["exposed_count"];
        $this->exposed_size = $row["exposed_size"];
        $this->date_created = $row["date_created"];
        $this->date_updated = $row["date_updated"];
        $this->thumb_type = $row["thumb_type"];
        $this->thumb_size = $row["thumb_size"];
        $this->thumb_width = $row["thumb_width"];
        $this->thumb_height = $row["thumb_height"];
        $this->is_hidden = $row["is_hidden"] == 0 ? false : true;
      }
    }

    public function isHidden(){return $this->is_hidden;}
    public function isValid(){return $this->valid;}
    public function setValid($b){$this->valid = $b;}
    public function getId(){return $this->id;}
    public function getName(){return $this->name;}
    public function setName($x){$this->name = $x;}
    public function getDescription(){return $this->description;}
    public function setDescription($x){$this->description = $x;}
    public function getPhotoCount(){return $this->photo_count;}
    public function getTotalSize(){return $this->total_size;}
    public function getExposedCount(){return $this->exposed_count;}
    public function getExposedSize(){return $this->exposed_size;}
    public function getDateCreated(){return $this->date_created;}
    public function getDateUpdated(){return $this->date_updated;}
    public function getThumbType(){return $this->thumb_type;}
    public function getThumbSize(){return $this->thumb_size;}
    public function getThumbWidth(){return $this->thumb_width;}
    public function getThumbHeight(){return $this->thumb_height;}
    public function getLastInsertedPhotoId(){return $this->last_inserted_photo_id;}
    
    public function dbRead($id){
      global $dbprefix;
      $db = getDatabase();
      $query = sprintf(
      "select\n" .
      "pa.id\n" .
      ",pa.name\n" .
      ",pa.description\n" .
      ",pa.photo_count\n" .
      ",pa.total_size\n" .
      ",pa.exposed_count\n" .
      ",pa.exposed_size\n" .
      ",pa.date_created\n" .
      ",pa.date_updated\n" .
      ",pa.thumb_type\n" .
      ",pa.thumb_size\n" .
      ",pa.thumb_width\n" .
      ",pa.thumb_height\n" .
      ",pa.is_hidden\n" .
      "from ".$dbprefix."_photoalbum pa\n" .
      "where pa.id=%d"
      ,$id
      );
      //trace($query);
      $rs = $db->query($query, __FILE__ . ":" . __LINE__);
      if($rs !== null){
        $row = $db->fetch_assoc($rs);
        if($row){
          $this->init($row);
          $db->free_result($rs);
          return true;
        }
      }
      throw new Exception("Не удалось обнаружить фотоальбом под номером ".$id);
    }

    public function dbReadPhotos($orderby = "name"){
      global $dbprefix;
      $orderby = strtolower($orderby);
      if($orderby == "name")
        $orderby = "p.photo_name asc,p.date_created desc";
      else if($orderby == "new")
        $orderby = "p.date_created desc";
      else if($orderby == "old")
        $orderby = "p.date_created asc";
      else
        $orderby = "p.photo_name asc,p.date_created desc";
      $db = getDatabase();
      $query = sprintf(
      self::photoFields().
      "where p.photoalbum_id=%d\n" .
      "%s" .
      "order by ".$orderby."\n"
      ,$this->id
      ,(User::isOperatorAdmin(User::getOperatorId()) ? "" : "and p.is_hidden=0\n")
      );
      //trace($query);
      $photos = array();
      $rs = $db->query($query, __FILE__ . ":" . __LINE__);
      while($rs && $row = $db->fetch_assoc($rs)){
        $photos[] = $row;
      }
      $db->free_result($rs);
      return $photos;
    }

		public function dbReadPhotosForSale($char,$status,$text){
      global $dbprefix;
		  $where = array();
		  
		  if($char && !is_numeric($text))
		    $where[] = self::clauseByChar($char);
		  else if($text)
		    $where[] = self::clauseSearch($text);
		  if($status && !is_numeric($text)) //if text is numeric - we search by exact ID, so any status filters are not applicable
		    $where[] = self::clauseStatus($status);
		  $query = self::photoFields().self::where($where).' order by p.photo_name';
      $db = getDatabase();
      return $db->query($query, __FILE__ . ":" . __LINE__);
		}
		
		private function where($clauses = array(), $connect = 'and'){
		  $s = '';
		  foreach($clauses as $c){
		    if(strlen($c) > 0){
			    if(strlen($s) > 0) $s .= " {$connect} ";
			    $s .= " {$c} ";
		    }
		  }
		  return ' where '.$s;
		}

		private function clauseStatus($status){
      return CactiStatus::sqlStatus($status);
//      
//      if(CactiStatus::status($status))
//      	return sprintf('p.status=%s',sqlsafe($status));
//      else
//      	return false;
		}

		private function clauseSearch($text){
      if(is_numeric($text))
      	return sprintf("(p.id=%d)\n",$text);
      else{
	      $text = mb_strtolower($text,'UTF-8');
	      return sprintf("(lower(p.photo_name) like '%%%s%%')\n",sqlsafe($text,false));
      }
		}

		private function clauseByChar($char){
      $char = mb_strtolower($char,'UTF-8');
      return sprintf("(lower(p.photo_name) like '%s%%')\n",$char);
		}

		private function clauseForAdmin(){
      return (User::isOperatorAdmin(User::getOperatorId()) ? "" : "((pa.is_hidden is null or pa.is_hidden=0) and p.is_hidden=0)\n");
		}

    public static function dbReadPhotosByChar($char,$orderby = "name"){
      global $dbprefix;
      $char = strtolower($char);
      if($orderby == "name")
        $orderby = "p.photo_name asc,p.date_created desc";
      else if($orderby == "new")
        $orderby = "p.date_created desc";
      else if($orderby == "old")
        $orderby = "p.date_created asc";
      else
        $orderby = "p.photo_name asc,p.date_created desc";
      $db = getDatabase();
      $query = sprintf(
      self::photoFields().
      
      "where lower(p.photo_name) like '%s%%'\n" .
      "%s" .
      "order by %s"
      ,$char
      ,(User::isOperatorAdmin(User::getOperatorId()) ? "" : "and (pa.is_hidden is null or pa.is_hidden=0) and p.is_hidden=0\n")
      ,$orderby
      );
      $rs = $db->query($query, __FILE__ . ":" . __LINE__);
      $photos = array();
      while($rs && $photo = $db->fetch_assoc($rs)){
      	
        $photo["info"] = htmlize(sprintf("%s\nДата создания %s\nРазмер %.1fКб",$photo["photo_file"],Localize::dbdate($photo["date_created"]),floatval($photo["photo_size"])/1024));
        $photos[] = $photo;
      }
      $db->free_result($rs);
      return $photos;
    }

		//???
    private static function searchClause($search){
      if(strlen($search) > 0){
        $search = strtolower($search);
        return sprintf(
        "lower(p.photo_name) like '%%%s%%'\n" .
        ""
        ,sqlsafe($search,false)
        );
      }
      else
        return "";
    }

    public static function dbSearchPhotos($search,$orderby = "name"){
      global $dbprefix;
      $photos = array();
      if(strlen($search) > 0){
        if($orderby == "name")
          $orderby = "p.photo_name asc,p.date_created desc";
        else if($orderby == "new")
          $orderby = "p.date_created desc";
        else if($orderby == "old")
          $orderby = "p.date_created asc";
        else
          $orderby = "p.photo_name asc,p.date_created desc";
        $db = getDatabase();
        $query = sprintf(
	      self::photoFields().
        "where %s\n" .
        "%s" .
        "order by %s"
        ,PhotoAlbum::searchClause($search)
        ,(User::isOperatorAdmin(User::getOperatorId()) ? "" : "and (pa.is_hidden is null or pa.is_hidden=0) and p.is_hidden=0\n")
        ,$orderby
        );
        $rs = $db->query($query, __FILE__ . ":" . __LINE__);
        $photos = array();
        while($rs && $photo = $db->fetch_assoc($rs)){
          $photo["info"] = htmlize(sprintf("%s\nДата создания %s\nРазмер %.1fКб",$photo["photo_file"],Localize::dbdate($photo["date_created"]),floatval($photo["photo_size"])/1024));
          $photos[] = $photo;
        }
        $db->free_result($rs);
      }
      return $photos;
    }

    public static function dbGetSlideList($ids,$orderby = "name"){
      global $dbprefix;
      if($orderby == "name")
        $orderby = "p.photo_name asc,p.date_created desc";
      else if($orderby == "new")
        $orderby = "p.date_created desc";
      else if($orderby == "old")
        $orderby = "p.date_created asc";
      else
        $orderby = "p.photo_name asc,p.date_created desc";
      $db = getDatabase();
      $query = sprintf(
      "select\n" .
      "p.id\n" .
      ",p.date_created\n" .
      ",p.thumb_type\n" .
      ",p.thumb_size\n" .
      ",p.thumb_width\n" .
      ",p.thumb_height\n" .
      ",p.photo_size\n" .
      ",p.photo_file\n" .
      ",p.photo_width\n" .
      ",p.photo_height\n" .
      ",p.photo_name\n" .
      ",p.photo_keywords\n" .
      ",p.photo_desc\n" .
      ",p.is_hidden\n" .
      "from ".$dbprefix."_photo p\n" .
      "where p.id in(%s)\n" .
      "%s" .
      "order by %s"
      ,$ids
      ,(User::isOperatorAdmin(User::getOperatorId()) ? "" : "and p.is_hidden=0\n")
      ,$orderby
      );
      $rs = $db->query($query, __FILE__ . ":" . __LINE__);
      $photos = null;
      while($rs && $photo = $db->fetch_assoc($rs))
        $photos[] = $photo;
        $db->free_result($rs);
      return $photos;
    }

    public static function dbGetSlideShowDimensions($ids,$availWidth,$availHeight){
      global $dbprefix;
      $db = getDatabase();
      $query = sprintf(
      "select\n" .
      "max(p.photo_width) as photo_width\n" .
      ",max(p.photo_height) as photo_height\n" .
      ",max(p.thumb_width) as thumb_width\n" .
      ",max(p.thumb_height) + 6 as thumb_height\n" .
      "from ".$dbprefix."_photo p\n" .
      "where p.id in(%s)\n" .
      "%s"
      ,$ids
      ,(User::isOperatorAdmin(User::getOperatorId()) ? "" : "and p.is_hidden=0")
      );
      //trace($query);
      $rs = $db->query($query, __FILE__ . ":" . __LINE__);
      $dim = null;
      if($rs && $dim = $db->fetch_assoc($rs))
        $db->free_result($rs);
      $dim["width"] = $dim["photo_width"];
      $dim["height"] = $dim["photo_height"] + 5 + $dim["thumb_height"] + 6;

      if($availHeight > 0 && $availWidth > 0){
      	if($dim["width"] > $availWidth - 10 || $dim["height"] > $availHeight - 70){
      		$dx = $dim["width"] - ($availWidth - 10);
          $dy = $dim["height"] - ($availHeight - 70);
          if($dx > $dy)
            $ratio = ($availWidth - 100)/$dim["width"];
          else
            $ratio = ($availHeight - 200)/$dim["height"];
          $dim["width"] = floor($dim["width"] * $ratio);
          $dim["height"] = floor($dim["height"] * $ratio);;
          $new_photo_height = $dim["height"] - (5 + $dim["thumb_height"] + 2);
          $ratio = $new_photo_height / $dim["photo_height"];
          $dim["photo_width"] = floor($dim["photo_width"] * $ratio);
          $dim["photo_height"] = floor($dim["photo_height"] * $ratio);;
          $dim["ratio"] = $ratio;
      	}
      }
      if(!isset($dim["ratio"]))
        $dim["ratio"] = 1;
      $dim["width"] = $dim["photo_width"];
      $dim["height"] = $dim["photo_height"] + 5 + $dim["thumb_height"] + 6;
      $dim["padding"] = 2;
      $dim["thumb_area"] = $dim["photo_width"] - 50;
      return $dim;
    }

    public static function printStats(){
      $stats = PhotoAlbum::dbGetPhotoGalleryStats();
?>
      <div class="italic S left mainColor">
        <p style="margin:0px;padding:0px;"><?php 
        printf(lang('Всего в фотогалерее [фотографий - %d, из них в фотоальбомах - %d, в прайс-листе - %d. Фотоальбомов - %d]'),$stats["album_photos"]+$stats["item_photos"],$stats["album_photos"],$stats["item_photos"],$stats["album_count"])?></p>
      </div>
<?php
    }

    public static function dbGetPhotoGalleryStats(){
      global $dbprefix;
      $db = getDatabase();
      $query = sprintf(
      "select\n" .
      "count(*) as album_photos\n" .
      "from ".$dbprefix."_photo\n" .
      "where photoalbum_id is not null\n" .
      "%s"
      ,(User::isOperatorAdmin(User::getOperatorId()) ? "" : "and is_hidden=0")
      );
      $rs = $db->query($query, __FILE__ . ":" . __LINE__);
      $stats = $db->fetch_assoc($rs);
      $db->free_result($rs);

      $query = sprintf(
      "select\n" .
      "count(*) as item_photos\n" .
      "from ".$dbprefix."_photo\n" .
      "where photoalbum_id is null\n"
      );
      $rs = $db->query($query, __FILE__ . ":" . __LINE__);
      $row = $db->fetch_assoc($rs);
      $stats["item_photos"] = $row["item_photos"];
      $db->free_result($rs);

      $query = sprintf(
      "select\n" .
      "count(*) as album_count\n" .
      "from ".$dbprefix."_photoalbum\n" .
      "%s"
      ,(User::isOperatorAdmin(User::getOperatorId()) ? "" : "where is_hidden=0")
      );
      $rs = $db->query($query, __FILE__ . ":" . __LINE__);
      $row = $db->fetch_assoc($rs);
      $stats["album_count"] = $row["album_count"];
      $db->free_result($rs);
      return $stats;
    }

    public function dbGetPhoto($photo_id){
      global $dbprefix;
      $db = getDatabase();
      $query = sprintf(
      "select\n" .
      "p.id\n" .
      ",p.date_created\n" .
      ",p.thumb_type\n" .
      ",p.thumb_size\n" .
      ",p.thumb_width\n" .
      ",p.thumb_height\n" .
      ",p.photo_size\n" .
      ",p.photo_file\n" .
      ",p.photo_name\n" .
      ",p.photo_keywords\n" .
      ",p.photo_desc\n" .
      ",p.is_hidden\n" .
      ",p.price as photo_price\n" .
      ",p.status as photo_status\n" .
      ",p.diameter\n" .
      ",p.notes\n" .
      "from ".$dbprefix."_photo p\n" .
      "where p.id=%d\n" .
      "%s" 
      ,$photo_id
      ,(User::isOperatorAdmin(User::getOperatorId()) ? "" : "and p.is_hidden=0\n")
      );
      $rs = $db->query($query, __FILE__ . ":" . __LINE__);
      $photo = null;
      if($rs && $photo = $db->fetch_assoc($rs))
        $db->free_result($rs);
      return $photo;
    }

    public function dbUpdatePhotoText($photo_id,$name,$desc,$keywords,$price,$status,$diameter,$notes){
      global $dbprefix;
      if(!$this->check())
        return false;
      $query = sprintf(
      "update ".$dbprefix."_photo\n" .
      "set\n" .
      "photo_name=%s\n" .
      ",photo_desc=%s\n" .
      ",photo_keywords=%s\n" .
      ",price=%.2f\n" .
      ",status=%s\n" .
      ",diameter=%.2f\n" .
      ",notes=%s\n" .
      "where id=%d and photoalbum_id=%d"
      ,sqlsafe($name)
      ,sqlsafe($desc)
      ,sqlsafe($keywords)
      ,$price
      ,sqlsafe($status)
      ,$diameter
      ,sqlsafe($notes)
      ,intval($photo_id)
      ,$this->getId()
      );
      //trace($query);
      $db = getDatabase();
      $db->query($query, __FILE__ . ":" . __LINE__ );
      return true;
    }

    public function dbInsert(){
      global $dbprefix;
      if(!$this->check())
        return false;
      $query = sprintf(
      "insert into ".$dbprefix."_photoalbum\n" .
      "(name\n" .
      ",description\n" .
      ",photo_count\n" .
      ",total_size\n" .
      ",date_created\n" .
      ",date_updated\n" .
      ")\n" .
      "values\n" .
      "(%s" .
      ",%s" .
      ",0" .
      ",0" .
      ",now()" .
      ",now()" .
      ")"
      ,sqlsafe($this->name)
      ,sqlsafe($this->description)
      );
      //trace($query);
      $db = getDatabase();
      $db->query($query, __FILE__ . ":" . __LINE__ );
      $this->id = $db->insert_id();
      return true;
    }
    
    public function dbUpdateText(){
      global $dbprefix;
      if(!$this->check())
        return false;
      $query = sprintf(
      "update ".$dbprefix."_photoalbum\n" .
      "set\n" .
      "name=%s\n" .
      ",description=%s\n" .
      "where id=%d"
      ,sqlsafe($this->name)
      ,sqlsafe($this->description)
      ,intval($this->id)
      );
      //trace($query);
      $db = getDatabase();
      $db->query($query, __FILE__ . ":" . __LINE__ );
      return true;
    }

    public function dbTouch(){
      global $dbprefix;
      if(!$this->check())
        return false;
      $query = sprintf(
      "update ".$dbprefix."_photoalbum\n" .
      "set\n" .
      "date_updated=now()\n" .
      "where id=%d"
      ,intval($this->id)
      );
      //trace($query);
      $db = getDatabase();
      $db->query($query, __FILE__ . ":" . __LINE__ );
      return true;
    }

    private function dbRecalculate(){
      global $dbprefix;
      if(!$this->check())
        return false;
      $db = getDatabase();
      $query = sprintf("select count(id) as photo_count,sum(photo_size) as total_size from ".$dbprefix."_photo where photoalbum_id=%d\n",intval($this->id));
      $rs = $db->query($query, __FILE__ . ":" . __LINE__ );
      $total = $db->fetch_assoc($rs);
      $db->free_result($rs);

      $query = sprintf("select count(id) as exposed_count,sum(photo_size) as exposed_size from ".$dbprefix."_photo where photoalbum_id=%d and is_hidden=0\n",intval($this->id));
      $rs = $db->query($query, __FILE__ . ":" . __LINE__ );
      $exposed = $db->fetch_assoc($rs);
      $db->free_result($rs);

      if($this->photo_count != $total["photo_count"] || $this->exposed_count != $exposed["exposed_count"]){
        $this->photo_count = $total["photo_count"];
        $this->total_size = $total["total_size"];
        $this->exposed_count = $exposed["exposed_count"];
        $this->exposed_size = $exposed["exposed_size"];
        $query = sprintf(
        "update ".$dbprefix."_photoalbum\n" .
        "set\n" .
        "photo_count=%d\n" .
        ",total_size=%d\n" .
        ",exposed_count=%d\n" .
        ",exposed_size=%d\n" .
        "where id=%d"
        ,$this->photo_count
        ,$this->total_size
        ,$this->exposed_count
        ,$this->exposed_size
        ,intval($this->id)
        );
        //trace($query);
        $db->query($query, __FILE__ . ":" . __LINE__ );
        if($this->photo_count == 1 && !is_null($this->getLastInsertedPhotoId()))
          $this->dbSetFrontendPhoto($this->getLastInsertedPhotoId());
      }
      return $this->photo_count;          
    }

    public function dbSetFrontendPhoto($photo_id){
      global $dbprefix;
      $db = getDatabase();
      $query = sprintf(
      "select count(id) from ".$dbprefix."_photo where photoalbum_id=%d and id=%d\n"
      ,$this->id
      ,$photo_id
      );
      $rs = $db->query($query, __FILE__ . ":" . __LINE__ );
      $row = $db->fetch_row($rs);
      $db->free_result($rs);
      if($row[0] > 0){
        $query = sprintf(
        "update ".$dbprefix."_photoalbum pa,".$dbprefix."_photo p\n" .
        "set\n" .
        "pa.thumb=p.thumb\n" .
        ",pa.thumb_type=p.thumb_type\n" .
        ",pa.thumb_size=p.thumb_size\n" .
        ",pa.thumb_width=p.thumb_width\n" .
        ",pa.thumb_height=p.thumb_height\n" .
        "where pa.id=%d and p.id=%d"
        ,$this->id
        ,$photo_id
        );
      }
      else{
      	setAlert(sprintf("Ошибка: фото с №%d не найдено",$photo_id));
      }
      //trace($query);
      $db->query($query, __FILE__ . ":" . __LINE__ );
      return true;
    }

    public function httpReadPhoto(){
      $max_thumb_width     = intval(getSetting("max_item_thumb_width",ITEM_THUMB_W));
      $max_thumb_height    = intval(getSetting("max_item_thumb_height",ITEM_THUMB_H));
      $max_photo_width     = intval(getSetting("max_item_photo_width",ITEM_PHOTO_W));
      $max_photo_height    = intval(getSetting("max_item_photo_height",ITEM_PHOTO_H));
      $error = null;
      $new_photo = null;
      if(getUploadedMedia($new_photo,$error,"photo",$max_photo_width,$max_photo_height,$max_thumb_width,$max_thumb_height)){
        if(strlen(getParameter("photo_name")) == 0){
          if(strpos($new_photo["name"],".") !== false){
            $new_photo["photo_name"] = substr($new_photo["name"],0,strpos($new_photo["name"],"."));
          }
          else
            $new_photo["photo_name"] = $new_photo["name"];
        }
        else{
          $new_photo["photo_name"] = getParameter("photo_name");
        }
        $new_photo["photo_keywords"] = getParameter("photo_keywords");
        $new_photo["photo_desc"] = getParameter("photo_desc");
        $new_photo["photo_price"] = getParameter("photo_price");
        $new_photo["photo_status"] = getParameter("photo_status");
        $new_photo["diameter"] = getParameter("diameter");
        $new_photo["notes"] = getParameter("notes");

        return $new_photo;
      }
      else{
        setAlert($error);
        return false;
      }
    }

    public function dbInsertPhoto(&$photo){
      global $dbprefix;
      if(strlen($photo["photo_status"]) == 0)
      	$photo["photo_status"] = 'unknown';
      if(strlen($photo["photo_name"]) == 0){
        $photo["photo_name"] = $photo["name"];
      }
      if(!is_null($photo)){
        $db = getDatabase();
        $query = sprintf("insert into ".$dbprefix."_photo\n" .
        "(date_created\n" .
        ",photo\n" .
        ",photo_name\n" .
        ",photo_keywords\n" .
        ",photo_desc\n" .
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
        ",photoalbum_id\n" .
        ",price\n" .
        ",status\n" .
        ",diameter\n" .
        ",notes\n" .
        ")\n" .
        "values\n" .
        "(now()\n" .  //date_created
        ",%s\n" .  //photo
        ",%s\n" .  //photo name
        ",%s\n" .  //photo keywords
        ",%s\n" .  //photo desc
        ",%s\n" .  //photo file
        ",%s\n" .  //photo type
        ",%d\n" .  //photo size
        ",%d\n" .  //photo width
        ",%d\n" .  //photo height
        ",%s\n" .  //thumb
        ",%s\n" .  //thumb type
        ",%d\n" .  //thumb size
        ",%d\n" .  //thumb width
        ",%d\n" .  //thumb height
        ",%d\n" .  //photoalbum_id
        ",%.2f\n" . //price
        ",%s\n" .  //status
        ",%.2f\n" . //diameter
        ",%s\n" .  //notes
        ")"
        ,(is_null($photo) ? "NULL" : "'".mysql_escape_string($photo["content"])."'")
        ,(is_null($photo) ? "NULL" : sqlsafe($photo["photo_name"]))
        ,(is_null($photo) ? "NULL" : sqlsafe($photo["photo_keywords"]))
        ,(is_null($photo) ? "NULL" : sqlsafe($photo["photo_desc"]))
        ,(is_null($photo) ? "NULL" : sqlsafe($photo["name"]))
        ,(is_null($photo) ? "NULL" : sqlsafe($photo["type"]))
        ,(is_null($photo) ? 0 : $photo["size"])
        ,(is_null($photo) ? 0 : $photo["width"])
        ,(is_null($photo) ? 0 : $photo["height"])
        ,(is_null($photo) ? "NULL" : "'".mysql_escape_string($photo["thumb_content"])."'")
        ,(is_null($photo) ? "NULL" : sqlsafe($photo["thumb_type"]))
        ,(is_null($photo) ? 0 : $photo["thumb_size"])
        ,(is_null($photo) ? 0 : $photo["thumb_width"])
        ,(is_null($photo) ? 0 : $photo["thumb_height"])
        ,$this->id
        ,$photo["photo_price"]
        ,sqlsafe($photo["photo_status"])
        ,$photo["diameter"]
        ,sqlsafe($photo["notes"])
        );
        $db->query($query,__FILE__.":".__LINE__);
        $this->last_inserted_photo_id = $db->insert_id();
        $this->dbTouch();
        $this->dbRecalculate();
        return $this->last_inserted_photo_id;
      }
    }
    
    public function dbDeletePhoto($photo_id){
      global $dbprefix;
      if(!is_null($this->id)){
        $db = getDatabase();
        $query = sprintf("delete from ".$dbprefix."_photo where photoalbum_id=%d and id=%d",$this->id,$photo_id);
        $db->query($query, __FILE__ . ":" . __LINE__);
        $this->dbTouch();
        $this->dbRecalculate();
        return true;
      }
      else{
        return false;
      }
    }

    public function dbMovePhoto($photo_id,$new_photoalbum_id){
      global $dbprefix;
      if(!is_null($this->id)){
        $db = getDatabase();
        $moveto = new PhotoAlbum();
        try{
          $moveto->dbRead($new_photoalbum_id);
          $query = sprintf("update ".$dbprefix."_photo set photoalbum_id=%d where id=%d",$new_photoalbum_id,$photo_id);
          $db->query($query, __FILE__ . ":" . __LINE__);
          $this->dbTouch();
          $this->dbRecalculate();
          $moveto->dbTouch();
          $moveto->dbRecalculate();
          return true;
        }
        catch(Exception $e){
          setAlert("Альбом, в который нужно переместить фото, не найден");
          return false;
        }
      }
      return false;
    }

    public function dbSetPhotoHidden($photo_id,$bHidden){
      global $dbprefix;
      if(!is_null($this->id)){
        $db = getDatabase();
        $query = sprintf("update ".$dbprefix."_photo set is_hidden=%d where photoalbum_id=%d and id=%d",$bHidden ? 1:0,$this->id,$photo_id);
        $db->query($query, __FILE__ . ":" . __LINE__);
        $this->dbTouch();
        $this->dbRecalculate();
        return true;
      }
      else{
        return false;
      }
    }

    public function dbDelete(){
      global $dbprefix;
      if(!is_null($this->id)){
        $db = getDatabase();
        $query = sprintf("delete from ".$dbprefix."_photo where photoalbum_id=%d",$this->id);
        $db->query($query, __FILE__ . ":" . __LINE__);
        $query = sprintf("delete from ".$dbprefix."_photoalbum where id=%d",$this->id);
        $db->query($query, __FILE__ . ":" . __LINE__);
        return true;
      }
      else{
        return false;
      }
    }
    
    public function dbSetHidden($bHidden){
      global $dbprefix;
      if(!is_null($this->id)){
        $db = getDatabase();
        $query = sprintf("update ".$dbprefix."_photoalbum set is_hidden=%d where id=%d",$bHidden ? 1 : 0,$this->id);
        $db->query($query, __FILE__ . ":" . __LINE__);
        return true;
      }
      else{
        return false;
      }
    }
    
    private function check(){
      if(!$this->valid)
        return false;
      if(strlen($this->name) == 0){
        $this->name = "Фотоальбом";
      }
      return $this->valid;
    }
    
    public static function getList($orderby){
      global $dbprefix;
      $db = getDatabase();
      $orderby = strtolower($orderby);
      if($orderby == "date_created")
        $orderby = "date_created desc";
      else if($orderby == "date_updated")
        $orderby = "date_updated desc";
      else if($orderby == "photo_count")
        $orderby = "photo_count desc";
      else if($orderby == "name")
        $orderby = "name asc";
      $query = "select pt.* from ".$dbprefix."_photoalbum pt ".$orderby;
      $rs = $db->query($query,__FILE__.":".__LINE__);
      return $rs;
    }
    
    public static function printSearchByNameControl(){
?>
      <div class="mainFont mainColor L" style="clear:both;padding:5px 0px">
        <div class="bold" style="float:left;margin-right:5px;line-height:22px;vertical-align:middle;"><?php hprint('Поиск по названию')?></div>
        <div style="float:left;"><input onkeypress="if(keyCode(event)==13){submitForm(this.form,'search')}" type="text" style="width:300px" name="search" value="" class="text"/></div>
        <div style="float:left;"><button name="search_btn" type="submit" style="margin:0px 3px;width:22px;height:22px;border:none;background:url(/images/btn-search.gif) no-repeat transparent;" onmouseover="this.style.backgroundPosition='0px -22px'" onmouseout="this.style.backgroundPosition='0px 0px'" onclick="this.style.backgroundPosition='0px -44px';submitForm(this.form,'search');"></button></div>
      </div>
<?php    	
    }
  }
?>