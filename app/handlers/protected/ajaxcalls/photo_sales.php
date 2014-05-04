<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("Localize.class.php");
  require_once("PhotoAlbum.class.php");
  require_once("CactiStatus.class.php");
  require_once("Services_JSON.class.php");
  require_once("common.inc.php");

  
  $data = null;
  $json = new Services_JSON();
  $char = getParameter('char');
  $status = getParameter('status');
  $text = getParameter('text');

	$rs = PhotoAlbum::dbReadPhotosForSale($char,$status,$text);
  $photos = array();
  $db = getDatabase();
  header("Expires: Mon, 1 Jan 1970 00:00:00 GMT");
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
  header("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
  header("Pragma: no-cache");                          // HTTP/1.0
  while($rs && $photo = $db->fetch_assoc($rs)){
    echo CactiStatus::renderPhotoWithControls($photo);
  }
  $db->free_result($rs);
//	
//	
//	
//  if(is_null($data) || !is_array($data) || count($data) == 0)
//    $data = array("error" => "Неизвестная ошибка (пустой результат)");
//  $data = $json->encode($data);
  flush();
?>
