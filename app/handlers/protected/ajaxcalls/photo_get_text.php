<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("Item.class.php");
  require_once("Localize.class.php");
  require_once("PhotoAlbum.class.php");
  require_once("Services_JSON.class.php");
  require_once("common.inc.php");

	$errorMsg = "Отсутствует необходимый параметр - номер фотоальбома или номер фото";
  $data = null;
  $json = new Services_JSON();
  if(isParameter("photoalbum_id") && isParameter("photo_id")){
    $photoalbum_id = getParameter("photoalbum_id");
    $photo_id = getParameter("photo_id");
    try{
      $album = new PhotoAlbum();
      $album->dbRead($photoalbum_id);
      $photo = $album->dbGetPhoto($photo_id);
      //$data = $json->encode(array("photo_name" => $photo["photo_name"],"photo_desc" => $photo["photo_desc"],"photo_keywords" => $photo["photo_keywords"]));
      $data = $json->encode($photo);
    }
    catch(Exception $e){
      $data = $json->encode(array("error" => $e->getMessage()));
    }
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
