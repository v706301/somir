<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("Localize.class.php");
  require_once("PhotoAlbum.class.php");
  require_once("CactiStatus.class.php");
  require_once("Services_JSON.class.php");
  require_once("common.inc.php");

	$errorMsg = "Отсутствует необходимый параметр - номер фотоальбома или номер фото";

  $data = null;
  $json = new Services_JSON();
  if(isParameter("photo_id")){
    $photo_id = getParameter("photo_id");
    if(isParameter("photoalbum_id"))
    	$photoalbum_id = getParameter("photoalbum_id");
    else{
      $db = getDatabase();
      $query = sprintf('select photoalbum_id from so_photo where id=%d',$photo_id);
      $rs = $db->query($query);
      if($rs && $row = $db->fetch_row($rs))
      	$photoalbum_id = $row[0];
      else
      	$photoalbum_id = false;
    }
    if($photoalbum_id){
	    try{
	      $album = new PhotoAlbum();
	      $album->dbRead($photoalbum_id);
	      $album->dbUpdatePhotoText($photo_id,getParameter("photo_name"),getParameter("photo_desc"),getParameter("photo_keywords"),getParameter("photo_price"),getParameter("photo_status"),getParameter("diameter"),getParameter("notes"));
	      $data = $json->encode(array("success" => CactiStatus::renderPhoto($album->dbGetPhoto($photo_id))));
	    }
	    catch(Exception $e){
	      $data = $json->encode(array("error" => $e->getMessage()));
	    }
    }
    else
    	$data = $json->encode(array("error" => lang($errorMsg)));
  }
  else{
    $data = $json->encode(array("error" => lang($errorMsg)));
  }

  header("Expires: Mon, 1 Jan 1970 00:00:00 GMT");
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
  header("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
  header("Pragma: no-cache");                          // HTTP/1.0
  print($data);
  flush();
?>
