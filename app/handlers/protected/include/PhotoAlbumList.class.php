<?php
  require_once("User.class.php");

  class PhotoAlbumList{
//    private $list = array();
//    private $id = null;
//    private $name = null;
//    private $description = null;
//    private $photo_count = null;
//    private $total_size = null;
//    private $date_created = null;
//    private $date_updated = null;
//    private $photo_id = null;
//    private $thumb_type = null;
//    private $thumb_size = null;
//    private $thumb_width = null;
//    private $thumb_height = null;
//    private $valid = null;

    public function __construct(){}

    private function searchClause($search){
      if(strlen($search) > 0){
      	$search = strtolower($search);
        return sprintf(
        "lower(ph.name) like %%%s%% or\n" .
        "lower(ph.description) like %%%s%% or\n" .
        "lower(pa.name) like %%%s%% or\n" .
        "lower(pa.name) like %%%s%%"
        ,$search
        ,$search
        ,$search
        ,$search
        );
      }
      else
        return "";
    }

    public function dbGetCount($filter = null){
      global $dbprefix;
      $db = getDatabase();
      if(is_null($filter))
        $filter = new stdClass();
      $search = property_exists($filter,"searchText") ? $this->searchClause($filter->searchText) : "";
      if(strlen($search) > 0)
        $query = sprintf(
        "select count(pa.id)\n" .
        "from ".$dbprefix."_photoalbum pa\n" .
        "left join ".$dbprefix."_photo ph on ph.photoalbum_id=pa.id\n" .
        "where %s%s\n" .
        "group by pa.id"
        ,(User::isOperatorAdmin(User::getOperatorId()) ? "" : "(pa.is_hidden is null or pa.is_hidden=0) and ")
        ,$search
        );
      else
        $query = sprintf(
        "select count(pa.id)\n" .
        "from ".$dbprefix."_photoalbum pa\n" .
        "%s"
        ,(User::isOperatorAdmin(User::getOperatorId()) ? "" : "where (pa.is_hidden is null or pa.is_hidden=0)")
        );
      $rs = $db->query($query,__FILE__.":".__LINE__);
      $row = $db->fetch_row($rs);
      $db->free_result($rs);
      return $row[0];
    }

    public function dbGetList($filter = null){
      global $dbprefix,$items_per_page,$start_row;
      $db = getDatabase();
      if(is_null($filter))
        $filter = new stdClass();
      $ascdesc = property_exists($filter,"ascdesc") ? $filter->ascdesc : "asc";
      $orderby = property_exists($filter,"orderby") ? strtolower($filter->orderby) : "pa.name";
      $search = property_exists($filter,"searchText") ? $this->searchClause($filter->searchText) : "";
      $limit = property_exists($filter,"itemsPerPage") ? $db->getLimitClause($filter->itemsPerPage,$filter->startRow) : "";
      if(strlen($search) > 0)
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
        "left join ".$dbprefix."_photo ph on ph.photoalbum_id=pa.id\n" .
        "where %s%s\n" .
        "group by pa.id\n" .
        "order by %s %s\n" .
        "%s"
        ,(User::isOperatorAdmin(User::getOperatorId()) ? "" : "(pa.is_hidden is null or pa.is_hidden=0) and ")
        ,$search
        ,$orderby
        ,$ascdesc
        ,$limit
        );
      else
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
        "%s" .
        "order by %s %s\n" .
        "%s"
        ,(User::isOperatorAdmin(User::getOperatorId()) ? "" : "where (pa.is_hidden is null or pa.is_hidden=0)\n")
        ,$orderby
        ,$ascdesc
        ,$limit
        );
      $rs = $db->query($query,__FILE__.":".__LINE__);
      $list = array();
      while($rs && $row = $db->fetch_assoc($rs))
        $list[] = $row;
      $db->free_result($rs);
      return $list;
    }

    public function dbGetLastUpdatedPhotoalbum(){
      global $dbprefix;
      $db = getDatabase();
      $query = 
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
      "order by pa.date_updated desc limit 1\n";
      $rs = $db->query($query,__FILE__.":".__LINE__);
      if($rs && $row = $db->fetch_assoc($rs)){
        $db->free_result($rs);
        return $row; 
      }
      else
        return false;
    }

    public function dbGetLastInsertedPhoto(){
      global $dbprefix;
      $db = getDatabase();
      $query = 
      "select\n" .
      "p.id as photo_id\n" .
      "p.date_created\n" .
      "p.photo_file\n" .
      "p.photo_type\n" .
      "p.photo_size\n" .
      "p.photo_width\n" .
      "p.photo_height\n" .
      "p.thumb_type\n" .
      "p.thumb_size\n" .
      "p.thumb_width\n" .
      "p.thumb_height\n" .
      "from ".$dbprefix."_photo p\n" .
      "where p.photoalbum_id is not null\n" .
      "order by p.date_created desc limit 1\n";
      $rs = $db->query($query,__FILE__.":".__LINE__);
      if($rs && $row = $db->fetch_assoc($rs)){
        $db->free_result($rs);
        return $row; 
      }
      else
        return false;
    }
  }
?>