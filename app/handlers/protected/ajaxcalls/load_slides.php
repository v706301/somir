<?php
  ini_set("include_path", ini_get("include_path") . ':' . __DIR__ . "/../include");
  require_once("Localize.class.php");
  require_once("PhotoAlbum.class.php");
  require_once("CactiStatus.class.php");
  require_once("Services_JSON.class.php");
  require_once("ShoppingCart.class.php");
  require_once("UserPreferences.class.php");
  require_once("common.inc.php");

  $prefs = new UserPreferences();
  $data = null;
  $json = new Services_JSON();
  if(isParameter("photos")){
    $photos = getParameter("photos");
    try{
      $list = PhotoAlbum::dbGetSlideList($photos);
      $data = $json->encode($list);
    }
    catch(Exception $e){
      $data = $json->encode(array("error" => $e->getMessage()));
    }
  }
  else{
    $status = $char = $search = false;
    if(getParameter('photoshop') == 1)
    	$status = array('available','sold','reserved');
    if(isParameter("char"))
    	$char = getParameter("char");
    if(isParameter("search"))
    	$search = getParameter("search");
    if(!($status || $char || $search))
    	$char = 'A';
    $rs = PhotoAlbum::dbReadPhotosForSale($char,$status,$search);
    $db = getDatabase();
    $data = '';
    while($rs && $row = $db->fetch_assoc($rs))
    	$data .= CactiStatus::renderForSale($row,$prefs);
  }

  header("Expires: Mon, 1 Jan 1970 00:00:00 GMT");
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
  header("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
  header("Pragma: no-cache");                          // HTTP/1.0
  print($data);
  flush();
?>
